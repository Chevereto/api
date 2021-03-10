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

use Chevere\Components\Workflow\Step;
use Chevere\Components\Workflow\Workflow;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Chevere\Interfaces\Workflow\WorkflowInterface;
use Chevereto\Actions\Database\DatabaseReserveRowAction;
use Chevereto\Actions\File\FileAssertNotDuplicateAction;
use Chevereto\Actions\File\FileNamingAction;
use Chevereto\Actions\File\FileUploadAction;
use Chevereto\Actions\File\FileValidateAction;
use Chevereto\Actions\Image\ImageFetchMetaAction;
use Chevereto\Actions\Image\ImageFixOrientationAction;
use Chevereto\Actions\Image\ImageInsertAction;
use Chevereto\Actions\Image\ImageStripMetaAction;
use Chevereto\Actions\Image\ImageValidateMediaAction;
use Chevereto\Actions\Storage\StorageGetForUserAction;
use Chevereto\Controllers\Api\V2\File\FilePostController;

abstract class ImagePostController extends FilePostController
{
    public function getWorkflow(): WorkflowInterface
    {
        return new Workflow(
            validateFile: new Step(
                FileValidateAction::class,
                mimes: '${mimes}',
                filepath: '${uploadFilepath}',
                maxBytes: '${maxBytes}',
                minBytes: '${minBytes}',
            ),
            validateMedia: new Step(
                ImageValidateMediaAction::class,
                filepath: '${uploadFilepath}',
                maxHeight: '${maxHeight}',
                maxWidth: '${maxWidth}',
                minHeight: '${minHeight}',
                minWidth: '${minWidth}',
            ),
            assertNotDuplicate: new Step(
                FileAssertNotDuplicateAction::class,
                md5: '${validateFile:md5}',
                perceptual: '${validateMedia:perceptual}',
                ip: '${ip}',
                ipVersion: '${ipVersion}',
            ),
            fixOrientation: new Step(
                ImageFixOrientationAction::class,
                image: '${validateMedia:image}'
            ),
            fetchMeta: new Step(
                ImageFetchMetaAction::class,
                image: '${validateMedia:image}'
            ),
            stripMeta: new Step(
                ImageStripMetaAction::class,
                image: '${validateMedia:image}'
            ),
            storageForUser: new Step(
                StorageGetForUserAction::class,
                userId: '${userId}',
                bytesRequired: '${validateFile:bytes}',
            ),
            reserveId: new Step(
                DatabaseReserveRowAction::class,
                table: '${table}',
            ),
            targetFilename: new Step(
                FileNamingAction::class,
                id: '${reserveId:id}',
                name: '${name}',
                naming: '${naming}',
                storage: '${storageForUser:storage}',
                path: '${path}'
            ),
            upload: new Step(
                FileUploadAction::class,
                filepath: '${uploadFilepath}',
                targetFilename: '${targetFilename:name}',
                storage: '${storageForUser:storage}',
                path: '${path}',
            ),
            insert: new Step(
                ImageInsertAction::class,
                id: '${reserveId:id}',
                albumId: '${albumId}',
                expires: '${expires}',
                userId: '${userId}',
            ),
        );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $uploadFilepath = tempnam(sys_get_temp_dir(), 'chv.temp');
        $this->assertStoreSource($arguments->getString('source'), $uploadFilepath);
        $settings = array_replace($arguments->toArray(), [
            'uploadFilepath' => $uploadFilepath,
        ]);

        return $this->getResponse();
    }
}
