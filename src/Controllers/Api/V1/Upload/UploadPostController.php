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

use Chevere\Components\Action\Controller;
use Chevere\Components\Dependent\Traits\DependentTrait;
use Chevere\Components\Message\Message;
use Chevere\Components\Parameter\IntegerParameter;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Parameter\StringParameter;
use Chevere\Components\Regex\Regex;
use Chevere\Components\Serialize\Unserialize;
use Chevere\Components\Workflow\Step;
use Chevere\Components\Workflow\Workflow;
use Chevere\Components\Workflow\WorkflowRun;
use Chevere\Components\Workflow\WorkflowRunner;
use function Chevere\Components\Workflow\workflowRunner;
use Chevere\Exceptions\Core\Exception;
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Interfaces\Dependent\DependentInterface;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Chevere\Interfaces\Workflow\WorkflowInterface;
use Chevereto\Actions\File\FileDetectDuplicateAction;
use Chevereto\Actions\File\FileUploadAction;
use Chevereto\Actions\File\FileValidateAction as ValidateFileAction;
use Chevereto\Actions\Image\ImageFetchMetaAction;
use Chevereto\Actions\Image\ImageFixOrientationAction;
use Chevereto\Actions\Image\ImageInsertAction;
use Chevereto\Actions\Image\ImageStripMetaAction;
use Chevereto\Actions\Image\ImageValidateMediaAction;
use Chevereto\Actions\Storage\StorageGetForUserAction;
use Chevereto\Controllers\Api\V2\File\Traits\FileStoreBase64SourceTrait;
use Laminas\Uri\UriFactory;
use function Safe\tempnam;

final class UploadPostController extends Controller implements DependentInterface
{
    use DependentTrait;
    use FileStoreBase64SourceTrait;

    private WorkflowInterface $workflow;

    public function getDescription(): string
    {
        return 'Uploads an image resource.';
    }

    public function getContextParameters(): ParametersInterface
    {
        return (new Parameters())
            ->withAddedRequired(
                apiV1Key: new StringParameter(),
                extensions: new StringParameter(),
                maxBytes: new IntegerParameter(),
                maxHeight: new IntegerParameter(),
                maxWidth: new IntegerParameter(),
                minBytes: new IntegerParameter(),
                minHeight: new IntegerParameter(),
                minWidth: new IntegerParameter(),
                naming: new StringParameter(),
                uploadPath: new StringParameter(),
                userId: new IntegerParameter()
            );
    }

    public function getParameters(): ParametersInterface
    {
        return (new Parameters())
            ->withAddedRequired(
                source: (new StringParameter())
                    ->withAddedAttribute('tryFiles')
                    ->withDescription('A base64 image string OR an image URL. It also takes image multipart/form-data.'),
                key: (new StringParameter())
                    ->withDescription('API V1 key.'),
            )
            ->withAddedOptional(
                format: (new StringParameter())
                    ->withRegex(new Regex('/^(json|txt)$/'))
                    ->withDefault('json')
                    ->withDescription('Response document output format. Defaults to `json`.'),
            );
    }

    public function getWorkflow(): WorkflowInterface
    {
        return (new Workflow(self::class))
            ->withAdded(
                validateFile: (new Step(ValidateFileAction::class))
                    ->withArguments(
                        extensions: '${extensions}',
                        filename: '${filename}',
                        maxBytes: '${maxBytes}',
                        minBytes: '${minBytes}',
                    ),
                validate: (new Step(ImageValidateMediaAction::class))
                    ->withArguments(
                        filename: '${filename}',
                        maxHeight: '${maxHeight}',
                        maxWidth: '${maxWidth}',
                        minHeight: '${minHeight}',
                        minWidth: '${minWidth}',
                    ),
                detectDuplication: (new Step(FileDetectDuplicateAction::class))
                    ->withArguments(
                        md5: '${validate:md5}',
                        perceptual: '${validate:perceptual}',
                        ip: '${ip}',
                        ipVersion: '${ipVersion}',
                    ),
                fixOrientation: (new Step(ImageFixOrientationAction::class))
                    ->withArguments(
                        image: '${validate:image}'
                    ),
                fetchMeta: (new Step(ImageFetchMetaAction::class))
                    ->withArguments(image: '${validate:image}'),
                stripMeta: (new Step(ImageStripMetaAction::class))
                    ->withArguments(
                        image: '${validate:image}'
                    ),
                storageForUser: (new Step(StorageGetForUserAction::class))
                    ->withArguments(
                        userId: '${userId}',
                        bytesRequired: '${validateFile:bytes}',
                    ),
                upload: (new Step(FileUploadAction::class))
                    ->withArguments(
                        filename: '${filename}',
                        naming: '${naming}',
                        originalName: '${originalName}',
                        storage: '${storageForUser:storage}',
                        uploadPath: '${uploadPath}',
                    ),
                insert: (new Step(ImageInsertAction::class))
                    ->withArguments(
                        albumId: '${albumId}',
                        expires: '${expires}',
                        userId: '${userId}',
                    )
            );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $context = $this->contextArguments();
        if ($arguments->getString('key') !== $context->getString('apiV1Key')) {
            throw new InvalidArgumentException(
                new Message('Invalid API V1 key provided'),
                100
            );
        }
        // $source will be a serialized PHP array if _FILES (+tryFiles attribute)
        $source = $arguments->getString('source');

        try {
            $unserialize = new Unserialize($source);
            $uploadFile = $unserialize->var()['tmp_name'];
        } catch (Exception $e) {
            $uploadFile = tempnam(sys_get_temp_dir(), 'chv.temp');
            $uri = UriFactory::factory($source);
            if ($uri->isValid()) {
                // G\fetch_url($source, $uploadFile);
            } else {
                $this->assertStoreSource($source, $uploadFile);
            }
        }
        $settings = array_replace($context->toArray(), [
            'filename' => $uploadFile,
            'albumId' => '',
        ]);
        unset($settings['apiV1Key']);
        $workflowRun = (new WorkflowRunner(
            new WorkflowRun($this->workflow, $settings)
        ))->run('container');
        $data = $workflowRun->get('upload')->data();
        if ($arguments->getString('format') === 'txt') {
            $raw = $data['url_viewer'];
        } else {
            $raw = json_encode($data, JSON_PRETTY_PRINT);
        }

        return $this->getResponse(raw: $raw);
    }
}
