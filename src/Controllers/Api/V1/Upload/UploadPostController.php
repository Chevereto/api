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
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Parameter\StringParameter;
use Chevere\Components\Regex\Regex;
use Chevere\Components\Serialize\Unserialize;
use Chevere\Components\Service\ServiceProviders;
use Chevere\Components\Workflow\Task;
use Chevere\Components\Workflow\WorkflowRun;
use Chevere\Exceptions\Core\Exception;
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Exceptions\Core\OutOfBoundsException;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseSuccessInterface;
use Chevere\Interfaces\Service\ServiceableInterface;
use Chevere\Interfaces\Service\ServiceProvidersInterface;
use Chevere\Interfaces\Workflow\WorkflowInterface;
use Chevereto\Actions\File\DetectDuplicateAction;
use Chevereto\Actions\File\UploadAction;
use Chevereto\Actions\File\ValidateAction as ValidateFileAction;
use Chevereto\Actions\Image\FetchMetaAction;
use Chevereto\Actions\Image\FixOrientationAction;
use Chevereto\Actions\Image\InsertAction;
use Chevereto\Actions\Image\StripMetaAction;
use Chevereto\Actions\Image\ValidateMediaAction;
use Chevereto\Actions\Storage\FailoverAction;
use Chevereto\Components\Settings;
use Chevereto\Controllers\Api\V2\File\Traits\FileStoreBase64SourceTrait;
use Laminas\Uri\UriFactory;
use function Chevere\Components\Workflow\workflowRunner;
use function Safe\fclose;
use function Safe\fopen;
use function Safe\fwrite;
use function Safe\stream_filter_append;
use function Safe\tempnam;

final class UploadPostController extends Controller implements ServiceableInterface
{
    use FileStoreBase64SourceTrait;

    private Settings $settings;

    private WorkflowInterface $workflow;

    public function getServiceProviders(): ServiceProvidersInterface
    {
        return (new ServiceProviders($this))
            ->withAdded('withSettings');
    }

    /**
     * @throws OutOfBoundsException
     */
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
            'uploadPath',
            'userId',
        );

        $new = clone $this;
        $new->settings = $settings;

        return $new;
    }

    public function settings(): Settings
    {
        return $this->settings;
    }

    public function getDescription(): string
    {
        return 'Uploads an image resource.';
    }

    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAddedRequired(
                (new StringParameter('source'))
                    ->withAddedAttribute('tryFiles')
                    ->withDescription('A base64 image string OR an image URL. It also takes image multipart/form-data.')
            )
            ->withAddedRequired(
                (new StringParameter('key'))
                    ->withDescription('API V1 key.')
            )
            ->withAddedOptional(
                (new StringParameter('format'))
                    ->withRegex(new Regex('/^(json|txt)$/'))
                    ->withDefault('json')
                    ->withDescription('Response document output format. Defaults to `json`.')
            );
    }

    /**
     * @return Array<string, Task>
     */
    public function getTasks(): array
    {
        return [
            'validate-file' => (new Task(ValidateFileAction::class))
                ->withArguments([
                    'extensions' => '${extensions}',
                    'filename' => '${filename}',
                    'maxBytes' => '${maxBytes}',
                    'minBytes' => '${minBytes}',
                ]),
            'validate' => (new Task(ValidateMediaAction::class))
                ->withArguments([
                    'filename' => '${filename}',
                    'maxHeight' => '${maxHeight}',
                    'maxWidth' => '${maxWidth}',
                    'minHeight' => '${minHeight}',
                    'minWidth' => '${minWidth}',
                ]),
            'detect-duplication' => (new Task(DetectDuplicateAction::class))
                ->withArguments([
                    'md5' => '${validate:md5}',
                    'perceptual' => '${validate:perceptual}',
                    'ip' => '${ip}',
                    'ipVersion' => '${ipVersion}',
                ]),
            'fix-orientation' => (new Task(FixOrientationAction::class))
                ->withArguments([
                    'image' => '${validate:image}'
                ]),
            'fetch-meta' => (new Task(FetchMetaAction::class))
                ->withArguments([
                    'image' => '${validate:image}'
                ]),
            'strip-meta' => (new Task(StripMetaAction::class))
                ->withArguments([
                    'image' => '${validate:image}'
                ]),
            'storage-failover' => (new Task(FailoverAction::class))
                ->withArguments([
                    'userId' => '${userId}',
                    'bytesRequired' => '${validate-file:bytes}',
                ]),
            'upload' => (new Task(UploadAction::class))
                ->withArguments([
                    'filename' => '${filename}',
                    'naming' => '${naming}',
                    'originalName' => '${originalName}',
                    'storageId' => '${storage-failover:storageId}',
                    'uploadPath' => '${uploadPath}',
                ]),
            'insert' => (new Task(InsertAction::class))
                ->withArguments([
                    'albumId' => '${albumId}',
                    // 'exif' => '${fetch-meta:exif}',
                    'expires' => '${expires}',
                    // 'image' => '${validate:image}',
                    // 'iptc' => '${fetch-meta:iptc}',
                    // 'md5' => '${validate:md5}',
                    // 'perceptual' => '${validate:perceptual}',
                    // 'storageId' => '${storage-failover:storageId}',
                    'userId' => '${userId}',
                    // 'xmp' => '${fetch-meta:xmp}',
                ])
        ];
    }

    public function run(array $arguments): ResponseSuccessInterface
    {
        $arguments = $this->getArguments($arguments);
        if ($arguments->getString('key') !== $this->settings->get('apiV1Key')) {
            throw new InvalidArgumentException(
                new Message('Invalid API V1 key provided'),
                100
            );
        }
        // $source will be a serialized PHP array if _FILES (+tryFiles attribute)
        $source = $arguments->getString('source');
        try {
            $unserialize = new Unserialize($source);
            $uploadFile = $unserialize->var()['tmp_name'];
        } catch (Exception $e) {
            $uploadFile = tempnam(sys_get_temp_dir(), 'chv.temp');
            $uri = UriFactory::factory($source);
            if ($uri->isValid()) {
                // G\fetch_url($source, $uploadFile);
            } else {
                $this->assertStoreSource($source, $uploadFile);
            }
        }
        $settings = $this->settings
            ->withPut('filename', $uploadFile)
            ->withPut('albumId', '');
        $settings = $settings->toArray();
        unset($settings['apiV1Key']);
        $workflowRun = workflowRunner(
            new WorkflowRun(
                $this->workflow,
                $settings
            )
        );
        $data = $workflowRun->get('upload')->data();
        if ($arguments->getString('format') === 'txt') {
            $raw = $data['url_viewer'];
        } else {
            $raw = json_encode($data, JSON_PRETTY_PRINT);
        }

        return $this->getResponseSuccess(['raw' => $raw]);
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
