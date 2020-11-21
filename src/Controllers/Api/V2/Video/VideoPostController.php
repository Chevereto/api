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

namespace Chevereto\Controllers\Api\V2\Video;

use Chevere\Components\Controller\Controller;
use Chevere\Components\Response\ResponseSuccess;
use Chevere\Components\Service\ServiceProviders;
use Chevere\Components\Workflow\Task;
use Chevere\Components\Workflow\Workflow;
use Chevere\Components\Workflow\WorkflowRun;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Chevere\Interfaces\Service\ServiceableInterface;
use Chevere\Interfaces\Service\ServiceProvidersInterface;
use Chevere\Interfaces\Workflow\TaskInterface;
use Chevere\Interfaces\Workflow\WorkflowInterface;
use Chevereto\Actions\Video\ValidateAction;
use Chevereto\Components\Settings;
use Chevereto\Controllers\Api\V2\File\FilePostController;
use Chevereto\Controllers\Api\V2\Video\Traits\VideoPostTrait;
use function Chevere\Components\Workflow\workflowRunner;

abstract class VideoPostController extends FilePostController
{
    public function getSettingsKeys(): array
    {
        return [
            'extensions',
            'maxBytes',
            'maxHeight',
            'maxLength',
            'maxWidth',
            'minBytes',
            'minHeight',
            'minLength',
            'minWidth',
            'naming',
            'storageId',
            'uploadPath',
            'userId'
        ];
    }

    public function getValidateTask(): TaskInterface
    {
        return (new Task(ValidateAction::class))
            ->withArguments([
                'filename' => '${filename}',
                'maxHeight' => '${maxHeight}',
                'maxWidth' => '${maxWidth}',
                'minHeight' => '${minHeight}',
                'minWidth' => '${minWidth}',
                'maxLength' => '${maxLength}',
                'minLength' => '${minLength}',
            ]);
    }

    // public function getFixOrientationTask(): TaskInterface
    // {
    //     return (new Task(FixOrientationAction::class))
    //         ->withArguments(['image' => '${validate:image}']);
    // }

    // public function getFetchMetaTask(): TaskInterface
    // {
    //     return (new Task(FetchMetaAction::class))
    //         ->withArguments(['image' => '${validate:image}']);
    // }

    // public function getStripMetaTask(): TaskInterface
    // {
    //     return (new Task(StripMetaAction::class))
    //         ->withArguments(['image' => '${validate:image}']);
    // }

    // public function getUploadTask(): TaskInterface
    // {
    //     return (new Task(UploadAction::class))
    //         ->withArguments([
    //             'image' => '${validate:image}',
    //             'naming' => '${naming}',
    //             'originalName' => '${originalName}',
    //             'storageId' => '${storage-failover:storageId}',
    //             'uploadPath' => '${uploadPath}',
    //         ]);
    // }

    // public function getInsertTask(): TaskInterface
    // {
    //     return (new Task(InsertAction::class))
    //         ->withArguments([
    //             // 'albumId' => '${albumId}',
    //             // 'exif' => '${fetch-meta:exif}',
    //             'expires' => '${expires}',
    //             // 'image' => '${validate:image}',
    //             // 'iptc' => '${fetch-meta:iptc}',
    //             // 'md5' => '${validate:md5}',
    //             // 'perceptual' => '${validate:perceptual}',
    //             // 'storageId' => '${storage-failover:storageId}',
    //             // 'userId' => '${userId}',
    //             // 'xmp' => '${fetch-meta:xmp}',
    //         ]);
    // }

    public function getWorkflow(): WorkflowInterface
    {
        return (new Workflow('upload-api-v1'))
            ->withAdded('validate-file', $this->getValidateFileTask())
            ->withAdded('validate', $this->getValidateTask())
            // Plug step
            ->withAdded('detect-duplication', $this->getDetectDuplicateTask())
            // ->withAdded('fix-orientation', $this->getFixOrientationTask())
            // ->withAdded('fetch-meta', $this->getFetchMetaTask())
            // Plug step
            // ->withAdded('strip-meta', $this->getStripMetaTask())
            // ->withAdded(
            //     'user-quota-check',
            //     (new Task())
            // )
            ->withAdded('storage-failover', $this->getStorageFailoverTask());
        // ->withAdded('upload', $this->getUploadTask())
            // ->withAdded('insert', $this->getInsertTask());
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $source = $arguments->get('source');

        $uploadFile = tempnam(sys_get_temp_dir(), 'chv.temp');
        $this->assertStoreSource($source, $uploadFile);
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
        $raw = json_encode($data, JSON_PRETTY_PRINT);

        return new ResponseSuccess([
            'raw' => $raw
        ]);
    }
}
