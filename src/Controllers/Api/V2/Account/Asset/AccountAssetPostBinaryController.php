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

namespace Chevereto\Controllers\Api\V2\Account\Asset;

use Chevere\Components\Workflow\Step;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\StringParameterInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Chevere\Interfaces\Workflow\WorkflowInterface;
use Chevereto\Actions\File\FileUploadAction;
use Chevereto\Actions\Image\ImageFixOrientationAction;
use Chevereto\Actions\Image\ImageStripMetaAction;
use Chevereto\Actions\Storage\StorageGetForAssetAction;
use Chevereto\Controllers\Api\V2\File\FilePostController;
use Chevereto\Controllers\Api\V2\File\Traits\FileStoreBinarySourceTrait;

abstract class AccountAssetPostBinaryController extends FilePostController
{
    use FileStoreBinarySourceTrait;

    abstract public function getBaseWorkflow(): WorkflowInterface;

    public function getWorkflow(): WorkflowInterface
    {
        return $this->getBaseWorkflow()
            ->withAdded(
                fixOrientation: new Step(
                    ImageFixOrientationAction::class,
                    image: '${validateMedia:image}'
                ),
                stripMeta: new Step(
                    ImageStripMetaAction::class,
                    image: '${validateMedia:image}'
                ),
                storageForAsset: new Step(
                    StorageGetForAssetAction::class,
                    userId: '${userId}',
                    bytesRequired: '${validateFile:bytes}',
                ),
                upload: new Step(
                    FileUploadAction::class,
                    filename: '${filename}',
                    targetFilename: '${asset:filename}',
                    storage: '${storageForAsset:storage}',
                    path: '${asset:path}',
                )
            );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $uploadFile = tempnam(sys_get_temp_dir(), 'chv.temp');
        $this->assertStoreSource($arguments->getString('source'), $uploadFile);
        $settings = array_replace($arguments->toArray(), [
            'filename' => $uploadFile,
        ]);

        return $this
            ->getResponse()
            ->withAddedAttribute('instant');

        return $this->getResponse();
    }

    public function getSourceParameter(): StringParameterInterface
    {
        return $this->getBinaryStringParameter()
            ->withDescription('A binary image.');
    }
}
