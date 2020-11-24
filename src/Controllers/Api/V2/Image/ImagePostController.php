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
use Chevere\Components\Workflow\Task;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Chevereto\Actions\File\DetectDuplicateAction;
use Chevereto\Actions\File\ValidateAction;
use Chevereto\Actions\Image\FetchMetaAction;
use Chevereto\Actions\Image\FixOrientationAction;
use Chevereto\Actions\Image\InsertAction;
use Chevereto\Actions\Image\StripMetaAction;
use Chevereto\Actions\Image\UploadAction;
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
                    'md5' => '${validate-media:md5}',
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
                    'storageId' => '${storageId}',
                    // 'required' => '${validate-media:bytes}'
                ]),
            'upload' => (new Task(UploadAction::class))
                ->withArguments([
                    'image' => '${validate-media:image}',
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

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $source = $arguments->get('source');
        $uploadFile = tempnam(sys_get_temp_dir(), 'chv.temp');
        $this->assertStoreSource($source, $uploadFile);
        $settings = $this->settings->withPut('filename', $uploadFile);
        $workflow = $this->getWorkflow('api-v2-image-post');

        return (new ResponseProvisional([]))
            ->withWorkflowMessage(
                getWorkflowMessage($workflow, $settings->toArray())
            );
    }
}
