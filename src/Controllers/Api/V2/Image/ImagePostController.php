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
use Chevereto\Actions\File\DetectDuplicateAction;
use Chevereto\Actions\File\UploadAction;
use Chevereto\Actions\File\ValidateAction;
use Chevereto\Actions\Image\FetchMetaAction;
use Chevereto\Actions\Image\FixOrientationAction;
use Chevereto\Actions\Image\InsertAction;
use Chevereto\Actions\Image\StripMetaAction;
use Chevereto\Actions\Image\ValidateMediaAction;
use Chevereto\Actions\Storage\FailoverAction;
use Chevereto\Controllers\Api\V2\File\FilePostController;
use Chevereto\Controllers\Api\V2\Image\Traits\ImageSettingsKeysTrait;
use function Chevere\Components\Workflow\getWorkflowMessage;

abstract class ImagePostController extends FilePostController
{
    use ImageSettingsKeysTrait;

    public function getSteps(): array
    {
        return [
            'validate-file' => (new Task(ValidateAction::class))
                ->withArguments([
                    'extensions' => '${extensions}',
                    'filename' => '${filename}',
                    'maxBytes' => '${maxBytes}',
                    'minBytes' => '${minBytes}',
                ]),
            'validate-media' => (new Task(ValidateMediaAction::class))
                ->withArguments([
                    'filename' => '${filename}',
                    'maxHeight' => '${maxHeight}',
                    'maxWidth' => '${maxWidth}',
                    'minHeight' => '${minHeight}',
                    'minWidth' => '${minWidth}',
                ]),
            'detect-duplicate' => (new Task(DetectDuplicateAction::class))
                ->withArguments([
                    'md5' => '${validate-file:md5}',
                    'perceptual' => '${validate-media:perceptual}',
                    'ip' => '${ip}',
                    'ipVersion' => '${ipVersion}',
                ]),
            'fix-orientation' => (new Task(FixOrientationAction::class))
                ->withArguments(['image' => '${validate-media:image}']),
            'fetch-meta' => (new Task(FetchMetaAction::class))
                ->withArguments(['image' => '${validate-media:image}']),
            'strip-meta' => (new Task(StripMetaAction::class))
                ->withArguments(['image' => '${validate-media:image}']),
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
