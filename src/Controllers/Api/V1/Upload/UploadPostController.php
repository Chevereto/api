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
use Chevere\Components\Response\ResponseSuccess;
use Chevere\Components\Serialize\Unserialize;
use Chevere\Components\Service\ServiceProviders;
use Chevere\Components\Str\StrBool;
use Chevere\Components\Workflow\Task;
use Chevere\Components\Workflow\Workflow;
use Chevere\Components\Workflow\WorkflowRun;
use Chevere\Exceptions\Core\Exception;
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Exceptions\Core\OutOfBoundsException;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Chevere\Interfaces\Service\ServiceableInterface;
use Chevere\Interfaces\Service\ServiceProvidersInterface;
use Chevere\Interfaces\Workflow\TaskInterface;
use Chevere\Interfaces\Workflow\WorkflowInterface;
use Chevereto\Actions\File\DetectDuplicateAction;
use Chevereto\Actions\File\ValidateAction as ValidateFileAction;
use Chevereto\Actions\Image\FetchMetaAction;
use Chevereto\Actions\Image\FixOrientationAction;
use Chevereto\Actions\Image\InsertAction;
use Chevereto\Actions\Image\StripMetaAction;
use Chevereto\Actions\Image\UploadAction;
use Chevereto\Actions\Image\ValidateAction;
use Chevereto\Actions\Storage\FailoverAction;
use Chevereto\Components\Settings;
use Laminas\Uri\UriFactory;
use function Chevere\Components\Workflow\workflowRunner;
use function Safe\fclose;
use function Safe\fopen;
use function Safe\fwrite;
use function Safe\stream_filter_append;
use function Safe\tempnam;

final class UploadPostController extends Controller implements ServiceableInterface
{
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
            'storageId',
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

    private function getValidateFileTask(): TaskInterface
    {
        return (new Task(ValidateFileAction::class))
            ->withArguments([
                'extensions' => '${extensions}',
                'filename' => '${filename}',
                'maxBytes' => '${maxBytes}',
                'minBytes' => '${minBytes}',
            ]);
    }

    private function getValidateTask(): TaskInterface
    {
        return (new Task(ValidateAction::class))
            ->withArguments([
                'filename' => '${filename}',
                'maxHeight' => '${maxHeight}',
                'maxWidth' => '${maxWidth}',
                'minHeight' => '${minHeight}',
                'minWidth' => '${minWidth}',
            ]);
    }

    private function getDetectDuplicateTask(): TaskInterface
    {
        return (new Task(DetectDuplicateAction::class))
            ->withArguments([
                'md5' => '${validate:md5}',
                'perceptual' => '${validate:perceptual}',
                'ip' => '${ip}',
                'ipVersion' => '${ipVersion}',
            ]);
    }

    private function getFixOrientationTask(): TaskInterface
    {
        return (new Task(FixOrientationAction::class))
            ->withArguments(['image' => '${validate:image}']);
    }

    private function getFetchMetaTask(): TaskInterface
    {
        return (new Task(FetchMetaAction::class))
            ->withArguments(['image' => '${validate:image}']);
    }

    private function getStripMetaTask(): TaskInterface
    {
        return (new Task(StripMetaAction::class))
            ->withArguments(['image' => '${validate:image}']);
    }

    private function getUploadTask(): TaskInterface
    {
        return (new Task(UploadAction::class))
            ->withArguments([
                'image' => '${validate:image}',
                'naming' => '${naming}',
                'originalName' => '${originalName}',
                'storageId' => '${storage-failover:storageId}',
                'uploadPath' => '${uploadPath}',
            ]);
    }

    private function getStorageFailoverTask(): TaskInterface
    {
        return (new Task(FailoverAction::class))
            ->withArguments([
                'storageId' => 0
            ]);
    }

    private function getInsertTask(): TaskInterface
    {
        return (new Task(InsertAction::class))
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
            ]);
    }

    public function getWorkflow(): WorkflowInterface
    {
        return (new Workflow('upload-api-v1'))
            ->withAdded('validate-file', $this->getValidateFileTask())
            ->withAdded('validate', $this->getValidateTask())
            // Plug step
            ->withAdded('detect-duplication', $this->getDetectDuplicateTask())
            ->withAdded('fix-orientation', $this->getFixOrientationTask())
            ->withAdded('fetch-meta', $this->getFetchMetaTask())
            // Plug step
            ->withAdded('strip-meta', $this->getStripMetaTask())
            // ->withAdded(
            //     'user-quota-check',
            //     (new Task())
            // )
            ->withAdded('storage-failover', $this->getStorageFailoverTask())
            ->withAdded('upload', $this->getUploadTask())
            ->withAdded('insert', $this->getInsertTask());
    }

    public function withWorkflow(WorkflowInterface $workflow): self
    {
        $new = clone $this;
        $new->workflow = $workflow;

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
        $settings = $settings->toArray();
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
