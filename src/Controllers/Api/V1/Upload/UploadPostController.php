<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Chevereto\Controllers\Api\V1\Upload;

use Chevere\Components\Controller\Controller;
use Chevere\Components\Message\Message;
use Chevere\Components\Parameter\ParameterOptional;
use Chevere\Components\Parameter\ParameterRequired;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Regex\Regex;
use Chevere\Components\Response\ResponseSuccess;
use Chevere\Components\Serialize\Unserialize;
use Chevere\Components\Service\ServiceProviders;
use Chevere\Components\Str\StrBool;
use Chevere\Components\Workflow\Task;
use Chevere\Components\Workflow\Workflow;
use Chevere\Components\Workflow\WorkflowRun;
use Chevere\Exceptions\Core\Exception;
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Interfaces\Controller\ControllerInterface;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Chevere\Interfaces\Service\ServiceableInterface;
use Chevere\Interfaces\Service\ServiceProvidersInterface;
use Chevere\Interfaces\Workflow\WorkflowInterface;
use Chevereto\Actions\File\ValidateFileAction;
use Chevereto\Actions\Image\UploadImageAction;
use Chevereto\Actions\Image\ValidateImageAction;
use Chevereto\Components\Settings;
use Chevereto\Components\User;
use Laminas\Uri\UriFactory;
use function Chevere\Components\Workflow\workflowRunner;
use function Safe\fclose;
use function Safe\fopen;
use function Safe\fwrite;
use function Safe\stream_filter_append;
use function Safe\tempnam;

final class UploadPostController extends Controller implements ServiceableInterface
{
    private User $user;

    private Settings $settings;

    private WorkflowInterface $workflow;

    public function getServiceProviders(): ServiceProvidersInterface
    {
        return (new ServiceProviders($this))
            ->withAdded('withSettings');
    }

    public function withSettings(Settings $settings): self
    {
        $settings->assertHasKey(
            'apiV1Key',
            'extensions',
            'maxBytes',
            'maxHeight',
            'maxWidth',
            'minBytes',
            'minHeight',
            'minWidth',
            'naming',
            'storageId',
            'uploadPath',
            'userId',
        );

        $new = clone $this;
        $new->settings = $settings;

        return $new;
    }

    public function getDescription(): string
    {
        return 'Uploads an image resource.';
    }

    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAdded(
                (new ParameterRequired('source'))
                    ->withDescription('A base64 image string OR an image URL. It also takes image multipart/form-data.')
            )
            ->withAdded(
                (new ParameterRequired('key'))
                    ->withDescription('API V1 key.')
            )
            ->withAdded(
                (new ParameterOptional('format'))
                    ->withAddedAttribute('tryFiles') // Flags controller runner to "try" the argument for _FILES
                    ->withRegex(new Regex('/^(json|txt)$/'))
                    ->withDefault('json')
                    ->withDescription('Response document output format. Defaults to `json`.')
            );
    }

    public function getWorkflow(): WorkflowInterface
    {
        return (new Workflow('upload-api-v1'))
            ->withAdded(
                'validate-file',
                (new Task(ValidateFileAction::class))
                    ->withArguments(
                        [
                            'extensions' => '${extensions}',
                            'filename' => '${filename}',
                            'maxBytes' => '${maxBytes}',
                            'minBytes' => '${minBytes}',
                        ]
                    )
            )
            ->withAdded(
                'validate-image',
                (new Task(ValidateImageAction::class))
                    ->withArguments(
                        [
                            'filename' => '${filename}',
                            'maxHeight' => '${maxHeight}',
                            'maxWidth' => '${maxWidth}',
                            'minHeight' => '${minHeight}',
                            'minWidth' => '${minWidth}',
                        ]
                    )
            )
            ->withAdded(
                'upload',
                (new Task(UploadImageAction::class))
                    ->withArguments([
                        'albumId' => '${albumId}',
                        'filename' => '${filename}',
                        'naming' => '${naming}',
                        'storageId' => '${storageId}',
                        'uploadPath' => '${uploadPath}',
                        'userId' => '${userId}',
                    ])
            );
    }

    public function withSetUp(): self
    {
        $new = clone $this;
        $new->workflow = $this->getWorkflow();

        return $new;
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        if ($arguments->get('key') !== $this->settings->get('apiV1Key')) {
            throw new InvalidArgumentException(
                new Message('Invalid API V1 key provided'),
                100
            );
        }
        // $source will be a serialized PHP array if _FILES (+tryFiles attribute)
        $source = $arguments->get('source');
        try {
            $unserialize = new Unserialize($source);
            $uploadFile = $unserialize->var()['tmp_name'];
        } catch (Exception $e) {
            $uploadFile = tempnam(sys_get_temp_dir(), 'chv.temp');
            $uri = UriFactory::factory($source);
            if ($uri->isValid()) {
                // G\fetch_url($source, $uploadFile);
            } else {
                try {
                    $this->assertBase64String($source);
                    $this->storeDecodedBase64String($source, $uploadFile);
                } catch (Exception $e) {
                    throw new InvalidArgumentException(
                        new Message('Invalid base64 string'),
                        $e->getCode()
                    );
                }
            }
        }
        $settings = $this->settings
            ->withPut('filename', $uploadFile)
            ->withPut('albumId', '');
        $settings = $settings->mapCopy()->toArray();
        unset($settings['apiV1Key']);
        $workflowRun = workflowRunner(
            new WorkflowRun(
                $this->workflow,
                $settings
            )
        );
        $data = $workflowRun->get('upload')->data();
        if ($arguments->get('format') === 'txt') {
            $raw = $data['url_viewer'];
        } else {
            $raw = json_encode($data, JSON_PRETTY_PRINT);
        }

        return new ResponseSuccess([
            'raw' => $raw
        ]);
    }

    public function assertBase64String(string $string): void
    {
        $double = base64_encode(base64_decode($string));
        if (!(new StrBool($string))->same($double)) {
            throw new Exception(
                new Message('Invalid base64 formatting'),
                1100
            );
        }
        unset($double);
    }

    public function storeDecodedBase64String(string $base64, string $path): void
    {
        $fh = fopen($path, 'w');
        stream_filter_append($fh, 'convert.base64-decode', STREAM_FILTER_WRITE);
        if (!fwrite($fh, $base64)) {
            throw new Exception(
                new Message('Unable to store decoded base64 string'),
                1200
            );
        }
        fclose($fh);
    }
}
