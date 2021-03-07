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

namespace Chevereto\Controllers\Api\V1\Upload;

use Chevere\Components\Controller\ControllerWorkflow;
use Chevere\Components\DataStructure\Map;
use Chevere\Components\Dependent\Traits\DependentTrait;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Parameter\StringParameter;
use Chevere\Components\Pluggable\Plug\Hook\Traits\PluggableHooksTrait;
use Chevere\Components\Pluggable\PluggableAnchors;
use Chevere\Components\Regex\Regex;
use Chevere\Components\Serialize\Deserialize;
use Chevere\Components\Workflow\Step;
use Chevere\Components\Workflow\Workflow;
use Chevere\Components\Workflow\WorkflowRun;
use Chevere\Components\Workflow\WorkflowRunner;
use function Chevere\Components\Workflow\workflowRunner;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Pluggable\Plug\Hook\PluggableHooksInterface;
use Chevere\Interfaces\Pluggable\PluggableAnchorsInterface;
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
use Chevereto\Actions\Legacy\ValidateApiV1KeyAction;
use Chevereto\Actions\Storage\StorageGetForUserAction;
use Chevereto\Controllers\Api\V2\File\Traits\FileStoreBase64SourceTrait;
use Laminas\Uri\UriFactory;
use function Safe\tempnam;
use Throwable;

final class UploadPostController extends ControllerWorkflow implements PluggableHooksInterface
{
    use DependentTrait;
    use PluggableHooksTrait;
    use FileStoreBase64SourceTrait;

    public function getDescription(): string
    {
        return 'Uploads an image resource.';
    }

    public static function getHookAnchors(): PluggableAnchorsInterface
    {
        return new PluggableAnchors(
            'getWorkflow:after',
        );
    }

    public function getResponseParameters(): ParametersInterface
    {
        return new Parameters(
            document: new StringParameter()
        );
    }

    public function getParameters(): ParametersInterface
    {
        return (new Parameters(
            source: (new StringParameter())
                ->withAddedAttribute('tryFiles')
                ->withDescription('A base64 image string OR an image URL. It also takes image multipart/form-data.'),
            key: (new StringParameter())
                ->withDescription('API V1 key.')
        ))
            ->withAddedOptional(
                format: (new StringParameter())
                    ->withRegex(new Regex('/^(json|txt)$/'))
                    ->withDefault('json')
                    ->withDescription('Response document output format. Defaults to `json`.'),
            );
    }

    public function getWorkflow(): WorkflowInterface
    {
        $workflow = new Workflow(
            validateApiV1Key: new Step(
                ValidateApiV1KeyAction::class,
                key: '${key}',
            ),
            validateFile: new Step(
                FileValidateAction::class,
                mimes: '${mimes}',
                filename: '${filename}',
                maxBytes: '${maxBytes}',
                minBytes: '${minBytes}',
            ),
            validateMedia: new Step(
                ImageValidateMediaAction::class,
                filename: '${filename}',
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
            targetBasename: new Step(
                FileTargetBasenameAction::class,
                id: '${reserveId:id}',
                name: '${name}',
                naming: '${naming}',
                storage: '${storageForUser:storage}',
                path: '${path}'
            ),
            upload: new Step(
                FileUploadAction::class,
                filename: '${filename}',
                targetBasename: '${targetBasename:name}',
                storage: '${storageForUser:storage}',
                path: '${path}',
            ),
            insert: new Step(
                ImageInsertAction::class,
                id: '${reserveId:id}',
                albumId: '${albumId}',
                expires: '${expires}',
                userId: '${userId}',
            )
        );
        $this->hook('getWorkflow:after', $workflow);

        return $workflow;
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $source = $arguments->getString('source');

        try {
            $deserialize = new Deserialize($source);
            $uploadFile = $deserialize->var()['tmp_name'];
        } catch (Throwable) {
            $uploadFile = tempnam(sys_get_temp_dir(), 'chv.temp');
            $uri = UriFactory::factory($source);
            if ($uri->isValid()) {
                // G\fetch_url($source, $uploadFile);
            } else {
                $this->assertStoreSource($source, $uploadFile);
            }
        }
        $workflowArguments = [
        ];

        return $this
            ->getResponse(
                key: $arguments->getString('key'),
                filename: $uploadFile,
                albumId: '',
            )
            ->withAddedAttribute('instant');

        // $run = new WorkflowRun($this->workflow, ...$workflowArguments);
        // $runner = (new WorkflowRunner($run))->run(new Map());
        // $data = $runner->get('upload')->data();
        // if ($arguments->getString('format') === 'txt') {
        //     $raw = $data['url_viewer'];
        // } else {
        //     $raw = json_encode($data, JSON_PRETTY_PRINT);
        // }

        // return $this->getResponse(raw: $raw);
    }
}
