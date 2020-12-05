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

use Chevere\Components\Parameter\IntegerParameter;
use Chevere\Components\Response\ResponseSuccess;
use Chevere\Components\Workflow\Step;
use Chevere\Components\Workflow\Workflow;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseSuccessInterface;
use Chevere\Interfaces\Workflow\WorkflowInterface;
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
use function Chevere\Components\Workflow\getWorkflowMessage;

abstract class ImagePostController extends FilePostController
{
    final public function getContextParameters(): ParametersInterface
    {
        return parent::getContextParameters()
            ->withAddedRequired(
                new IntegerParameter('maxHeight'),
                new IntegerParameter('maxWidth'),
                new IntegerParameter('minHeight'),
                new IntegerParameter('minWidth'),
            );
    }

    public function getWorkflow(): WorkflowInterface
    {
        return (new Workflow(__CLASS__))
            ->withAdded(
                (new Step('validate-file', FileValidateAction::class))
                    ->withArguments([
                        'extensions' => '${extensions}',
                        'filename' => '${filename}',
                        'maxBytes' => '${maxBytes}',
                        'minBytes' => '${minBytes}',
                    ]),
                (new Step('validate-media', ImageValidateMediaAction::class))
                    ->withArguments([
                        'filename' => '${filename}',
                        'maxHeight' => '${maxHeight}',
                        'maxWidth' => '${maxWidth}',
                        'minHeight' => '${minHeight}',
                        'minWidth' => '${minWidth}',
                    ]),
                (new Step('detect-duplicate', FileDetectDuplicateAction::class))
                    ->withArguments([
                        'md5' => '${validate-file:md5}',
                        'perceptual' => '${validate-media:perceptual}',
                        'ip' => '${ip}',
                        'ipVersion' => '${ipVersion}',
                    ]),
                (new Step('fix-orientation', ImageFixOrientationAction::class))
                    ->withArguments(['image' => '${validate-media:image}']),
                (new Step('fetch-meta', ImageFetchMetaAction::class))
                    ->withArguments(['image' => '${validate-media:image}']),
                (new Step('strip-meta', ImageStripMetaAction::class))
                    ->withArguments(['image' => '${validate-media:image}']),
                (new Step('storage-for-user', StorageGetForUserAction::class))
                    ->withArguments([
                        'userId' => '${userId}',
                        'bytesRequired' => '${validate-file:bytes}',
                    ]),
                (new Step('upload', FileUploadAction::class))
                    ->withArguments([
                        'filename' => '${filename}',
                        'naming' => '${naming}',
                        'originalName' => '${originalName}',
                        'storage' => '${storage-for-user:storage}',
                        'uploadPath' => '${uploadPath}',
                    ]),
                (new Step('insert', ImageInsertAction::class))
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
            );
    }

    public function run(array $arguments): ResponseSuccessInterface
    {
        $context = $this->contextArguments();
        $arguments = $this->getArguments($arguments);
        $uploadFile = tempnam(sys_get_temp_dir(), 'chv.temp');
        $this->assertStoreSource($arguments->getString('source'), $uploadFile);
        $settings = array_replace($context->toArray(), ['filename' => $uploadFile]);

        return (new ResponseSuccess($this->getResponseDataParameters(), []))
            ->withWorkflowMessage(
                getWorkflowMessage($this->getWorkflow(), $settings)
            );
    }
}
