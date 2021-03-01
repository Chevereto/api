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
use function Chevere\Components\Workflow\getWorkflowMessage;
use Chevere\Components\Workflow\Step;
use Chevere\Components\Workflow\Workflow;
use Chevere\Components\Workflow\WorkflowResponse;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Chevere\Interfaces\Workflow\WorkflowInterface;
use Chevereto\Actions\Database\DatabaseReserveRowAction;
use Chevereto\Actions\File\FileAssertNotDuplicateAction;
use Chevereto\Actions\File\FileTargetBasenameAction;
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
    final public function getContextParameters(): ParametersInterface
    {
        return parent::getContextParameters()
            ->withAdded(
                maxHeight: new IntegerParameter(),
                maxWidth: new IntegerParameter(),
                minHeight: new IntegerParameter(),
                minWidth: new IntegerParameter(),
            );
    }

    public function getWorkflow(): WorkflowInterface
    {
        return (new Workflow(static::class))
            ->withAdded(
                validateFile: (new Step(FileValidateAction::class))
                    ->withArguments(
                        mimes: '${mimes}',
                        filename: '${filename}',
                        maxBytes: '${maxBytes}',
                        minBytes: '${minBytes}',
                    ),
                validateMedia: (new Step(ImageValidateMediaAction::class))
                    ->withArguments(
                        filename: '${filename}',
                        maxHeight: '${maxHeight}',
                        maxWidth: '${maxWidth}',
                        minHeight: '${minHeight}',
                        minWidth: '${minWidth}',
                    ),
                assertNotDuplicate: (new Step(FileAssertNotDuplicateAction::class))
                    ->withArguments(
                        md5: '${validateFile:md5}',
                        perceptual: '${validateMedia:perceptual}',
                        ip: '${ip}',
                        ipVersion: '${ipVersion}',
                    ),
                fixOrientation: (new Step(ImageFixOrientationAction::class))
                    ->withArguments(image: '${validateMedia:image}'),
                fetchMeta: (new Step(ImageFetchMetaAction::class))
                    ->withArguments(image: '${validateMedia:image}'),
                stripMeta: (new Step(ImageStripMetaAction::class))
                    ->withArguments(image: '${validateMedia:image}'),
                storageForUser: (new Step(StorageGetForUserAction::class))
                    ->withArguments(
                        userId: '${userId}',
                        bytesRequired: '${validateFile:bytes}',
                    ),
                reserveId: (new Step(DatabaseReserveRowAction::class))
                    ->withArguments(
                        table: '${table}',
                    ),
                targetBasename: (new Step(FileTargetBasenameAction::class))
                    ->withArguments(
                        id: '${reserveId:id}',
                        name: '${name}',
                        naming: '${naming}',
                        storage: '${storageForUser:storage}',
                        path: '${path}'
                    ),
                upload: (new Step(FileUploadAction::class))
                    ->withArguments(
                        filename: '${filename}',
                        targetBasename: '${targetBasename:name}',
                        storage: '${storageForUser:storage}',
                        path: '${path}',
                    ),
                insert: (new Step(ImageInsertAction::class))
                    ->withArguments(
                        id: '${reserveId:id}',
                        albumId: '${albumId}',
                        expires: '${expires}',
                        userId: '${userId}',
                    ),
            );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $context = $this->contextArguments();
        $uploadFile = tempnam(sys_get_temp_dir(), 'chv.temp');
        $this->assertStoreSource($arguments->getString('source'), $uploadFile);
        $settings = array_replace($context->toArray(), [
            'filename' => $uploadFile,
        ]);

        return (new WorkflowResponse($this->getResponseDataParameters(), []))
            ->withWorkflowMessage(
                getWorkflowMessage($this->getWorkflow(), ...$settings)
            );
    }
}
