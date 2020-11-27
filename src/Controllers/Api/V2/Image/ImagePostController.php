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

use Chevere\Components\Response\ResponseSuccess;
use Chevere\Components\Workflow\Task;
use Chevere\Interfaces\Response\ResponseSuccessInterface;
use Chevereto\Actions\File\FileDetectDuplicateAction;
use Chevereto\Actions\File\FileUploadAction;
use Chevereto\Actions\File\FileValidateAction;
use Chevereto\Actions\Image\ImageFetchMetaAction;
use Chevereto\Actions\Image\ImageFixOrientationAction;
use Chevereto\Actions\Image\ImageInsertAction;
use Chevereto\Actions\Image\ImageStripMetaAction;
use Chevereto\Actions\Image\ImageValidateMediaAction;
use Chevereto\Actions\Storage\StorageGetForUserAction;
use Chevereto\Controllers\Api\V2\File\FilePostController;
use Chevereto\Controllers\Api\V2\Image\Traits\ImageSettingsKeysTrait;
use function Chevere\Components\Workflow\getWorkflowMessage;

abstract class ImagePostController extends FilePostController
{
    use ImageSettingsKeysTrait;

    public function getSteps(): array
    {
        return [
            'validate-file' => (new Task(FileValidateAction::class))
                ->withArguments([
                    'extensions' => '${extensions}',
                    'filename' => '${filename}',
                    'maxBytes' => '${maxBytes}',
                    'minBytes' => '${minBytes}',
                ]),
            'validate-media' => (new Task(ImageValidateMediaAction::class))
                ->withArguments([
                    'filename' => '${filename}',
                    'maxHeight' => '${maxHeight}',
                    'maxWidth' => '${maxWidth}',
                    'minHeight' => '${minHeight}',
                    'minWidth' => '${minWidth}',
                ]),
            'detect-duplicate' => (new Task(FileDetectDuplicateAction::class))
                ->withArguments([
                    'md5' => '${validate-file:md5}',
                    'perceptual' => '${validate-media:perceptual}',
                    'ip' => '${ip}',
                    'ipVersion' => '${ipVersion}',
                ]),
            'fix-orientation' => (new Task(ImageFixOrientationAction::class))
                ->withArguments(['image' => '${validate-media:image}']),
            'fetch-meta' => (new Task(ImageFetchMetaAction::class))
                ->withArguments(['image' => '${validate-media:image}']),
            'strip-meta' => (new Task(ImageStripMetaAction::class))
                ->withArguments(['image' => '${validate-media:image}']),
            'storage-for-user' => (new Task(StorageGetForUserAction::class))
                ->withArguments([
                    'userId' => '${userId}',
                    'bytesRequired' => '${validate-file:bytes}',
                ]),
            'upload' => (new Task(FileUploadAction::class))
                ->withArguments([
                    'filename' => '${filename}',
                    'naming' => '${naming}',
                    'originalName' => '${originalName}',
                    'storage' => '${storage-for-user:storage}',
                    'uploadPath' => '${uploadPath}',
                ]),
            'insert' => (new Task(ImageInsertAction::class))
                ->withArguments([
                    'albumId' => '${albumId}',
                    // 'exif' => '${fetch-meta:exif}',
                    'expires' => '${expires}',
                    // 'image' => '${validate:image}',
                    // 'iptc' => '${fetch-meta:iptc}',
                    // 'md5' => '${validate:md5}',
                    // 'perceptual' => '${validate:perceptual}',
                    // 'storageId' => '${storage-for-user:storageId}',
                    'userId' => '${userId}',
                    // 'xmp' => '${fetch-meta:xmp}',
                ]),
        ];
    }

    public function run(array $arguments): ResponseSuccessInterface
    {
        $arguments = $this->getArguments($arguments);
        $uploadFile = tempnam(sys_get_temp_dir(), 'chv.temp');
        $this->assertStoreSource($arguments->getString('source'), $uploadFile);
        $settings = $this->settings->withPut('filename', $uploadFile);

        return (new ResponseSuccess($this->getResponseDataParameters(), []))
            ->withWorkflowMessage(
                getWorkflowMessage($this->getWorkflow(), $settings->toArray())
            );
    }
}
