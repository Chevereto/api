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

namespace Chevereto\Controllers\ApiV1\Upload;

use Chevere\Components\Controller\Controller;
use Chevere\Components\Parameter\Parameter;
use Chevere\Components\Parameter\ParameterOptional;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Plugin\PluggableAnchors;
use Chevere\Components\Plugin\Plugs\Hooks\Traits\PluggableHooksTrait;
use Chevere\Components\Regex\Regex;
use Chevere\Components\Response\ResponseSuccess;
use Chevere\Components\Workflow\Task;
use Chevere\Components\Workflow\Workflow;
use Chevere\Components\Workflow\WorkflowRun;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Plugin\PluggableAnchorsInterface;
use Chevere\Interfaces\Plugin\Plugs\Hooks\PluggableHooksInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Chevere\Interfaces\Workflow\WorkflowInterface;
use function Chevere\Components\Workflow\workflowRunner;

function validateImageFn(string $filename): ResponseInterface
{
    return new ResponseSuccess([]);
}

function uploadImageFn(string $filename): ResponseInterface
{
    return new ResponseSuccess([
        'id' => '123',
    ]);
}

final class UploadGetController extends Controller implements PluggableHooksInterface
{
    use PluggableHooksTrait;

    private WorkflowInterface $workflow;

    public static function getHookAnchors(): PluggableAnchorsInterface
    {
        return (new PluggableAnchors)
            ->withAdded('setWorkflow')
            ->withAdded('beforeResponse');
    }

    public function getDescription(): string
    {
        return 'Uploads an image resource.';
    }

    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAdded(
                (new Parameter('source', new Regex('/.*/')))
                    ->withDescription('A base64 image string OR an image URL or a FILES single resource.')
            )
            ->withAdded(
                (new Parameter('key', new Regex('/.*/')))
                    ->withDescription('API V1 key.')
            )
            ->withAdded(
                (new ParameterOptional('format', new Regex('/^(json|redirect|txt)$/')))
                    ->withDescription('Response document output format. Defaults to `json`.')
            );
    }

    public function getWorkflow(): WorkflowInterface
    {
        return (new Workflow('apiv1-upload-get-controller'))
            ->withAdded(
                'validate',
                (new Task('Chevereto\Controllers\ApiV1\Upload\validateImageFn'))
                    ->withArguments('${filename}')
            )
            ->withAdded(
                'upload',
                (new Task('Chevereto\Controllers\ApiV1\Upload\uploadImageFn'))
                    ->withArguments('${filename}')
            );
        // $workflow = $workflow
        //     // Plugin: check banned hashes
        //     ->withAddedBefore(
        //         'validate',
        //         'vendor-ban-check',
        //         (new Task('vendorPath/banCheck'))
        //             ->withArguments('${filename}')
        //     )
        //     // Plugin: sepia filter
        //     ->withAddedAfter(
        //         'validate',
        //         'vendor-sepia-filter',
        //         (new Task('vendorPath/sepiaFilter'))
        //             ->withArguments('${filename}')
        //     );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $workflow = $this->getWorkflow();
        $this->hook('setWorkflow', $workflow);
        /**
         * @var string $source
         */
        $source = $arguments->get('source');
        $filename = "$source.tmp";
        $workflowRun = new WorkflowRun(
            $workflow,
            ['filename' => $filename]
        );
        $workflowRun = workflowRunner($workflowRun);
        $uploadId = $workflowRun->get('upload')->data()['id'];
        $image = [
            'id' => $uploadId,
        ];
        $this->hook('beforeResponse', $image);

        return new ResponseSuccess($image);
    }
}
