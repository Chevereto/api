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

namespace Chevereto\Controllers\Api\V2\Image;

use Chevere\Components\Response\ResponseProvisional;
use Chevere\Components\Workflow\Workflow;
use Chevere\Components\Workflow\WorkflowMessage;
use Chevere\Components\Workflow\WorkflowRun;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Chevere\Interfaces\Workflow\WorkflowInterface;
use Chevereto\Controllers\Api\V2\File\FilePostController;
use Chevereto\Controllers\Api\V2\Image\Traits\ImageGetFetchMetaTaskTrait;
use Chevereto\Controllers\Api\V2\Image\Traits\ImageGetFixOrientationTaskTrait;
use Chevereto\Controllers\Api\V2\Image\Traits\ImageGetInsertTaskTrait;
use Chevereto\Controllers\Api\V2\Image\Traits\ImageGetSettingsKeysTrait;
use Chevereto\Controllers\Api\V2\Image\Traits\ImageGetStripMetaTaskTrait;
use Chevereto\Controllers\Api\V2\Image\Traits\ImageGetUploadTaskTrait;
use Chevereto\Controllers\Api\V2\Image\Traits\ImageGetValidateTaskTrait;

abstract class ImagePostController extends FilePostController
{
    use ImageGetSettingsKeysTrait, ImageGetValidateTaskTrait, ImageGetFixOrientationTaskTrait, ImageGetFetchMetaTaskTrait, ImageGetStripMetaTaskTrait, ImageGetUploadTaskTrait, ImageGetInsertTaskTrait;

    final public function getWorkflow(): WorkflowInterface
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

    final public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $source = $arguments->get('source');
        $uploadFile = tempnam(sys_get_temp_dir(), 'chv.temp');
        $this->assertStoreSource($source, $uploadFile);
        $settings = $this->settings
            ->withPut('filename', $uploadFile);
        $workflowMessage = new WorkflowMessage(
            new WorkflowRun($this->workflow, $settings->toArray())
        );
        $data = [
            'delay' => $workflowMessage->delay(),
            'expiration' => $workflowMessage->expiration(),
        ];
        $response = new ResponseProvisional($data);
        ($this->enqueue)($workflowMessage, $response);

        return $response;
    }
}
