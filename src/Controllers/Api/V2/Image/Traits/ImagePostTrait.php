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

namespace Chevereto\Controllers\Api\V2\Image\Traits;

use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Parameter\StringParameter;
use Chevere\Components\Response\ResponseSuccess;
use Chevere\Components\Service\ServiceProviders;
use Chevere\Components\Workflow\Task;
use Chevere\Components\Workflow\Workflow;
use Chevere\Components\Workflow\WorkflowRun;
use Chevere\Exceptions\Core\OutOfBoundsException;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Chevere\Interfaces\Response\ResponseSuccessInterface;
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
use function Chevere\Components\Workflow\workflowRunner;

trait ImagePostTrait
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
            ->withAddedRequired(new StringParameter('source'));
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
                'ipv4' => '${ipv4}',
                'ipv6' => '${ipv6}',
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
                // 'albumId' => '${albumId}',
                // 'exif' => '${fetch-meta:exif}',
                'expires' => '${expires}',
                // 'image' => '${validate:image}',
                // 'iptc' => '${fetch-meta:iptc}',
                // 'md5' => '${validate:md5}',
                // 'perceptual' => '${validate:perceptual}',
                // 'storageId' => '${storage-failover:storageId}',
                // 'userId' => '${userId}',
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

    abstract public function assertStoreSource($source, string $uploadFile): void;

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $source = $arguments->get('source');

        $uploadFile = tempnam(sys_get_temp_dir(), 'chv.temp');
        $this->assertStoreSource($source, $uploadFile);
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
        $raw = json_encode($data, JSON_PRETTY_PRINT);

        return new ResponseSuccess([
            'raw' => $raw
        ]);
    }
}
