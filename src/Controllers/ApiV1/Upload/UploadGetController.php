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
use Chevere\Components\Parameter\Arguments;
use Chevere\Components\Parameter\Parameter;
use Chevere\Components\Parameter\ParameterOptional;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Regex\Regex;
use Chevere\Components\Response\ResponseSuccess;
use Chevere\Components\Service\ServiceProviders;
use Chevere\Components\Workflow\Task;
use Chevere\Components\Workflow\Workflow;
use Chevere\Components\Workflow\WorkflowRun;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Chevere\Interfaces\Service\ServiceableInterface;
use Chevere\Interfaces\Service\ServiceProvidersInterface;
use Chevere\Interfaces\Workflow\WorkflowInterface;

final class UploadGetController extends Controller implements ServiceableInterface
{
    private WorkflowInterface $workflow;

    public function getServiceProviders(): ServiceProvidersInterface
    {
        return (new ServiceProviders($this))
            ->withAdded('withWorkflow');
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

    public function withWorkflow(WorkflowInterface $workflow): self
    {
        $new = clone $this;
        $new->workflow = $workflow;

        return $new;
    }

    public function getWorkflow(): WorkflowInterface
    {
        return (new Workflow('apiv1-upload-get-controller'))
            ->withAdded(
                'fetch',
                (new Task('fetchApiV1ImageFn'))
                    ->withArguments('${source}') // argument too huge for serialize!
            )
            ->withAdded(
                'validate',
                (new Task('validateImageFn'))
                    ->withArguments('${filename}')
            )
            ->withAdded(
                'upload',
                (new Task('uploadImageFn'))
                    ->withArguments('${filename}')
            )
            )
            ->withAdded(
                'response',
                (new Task('picoConLaWea'))
                    ->withArguments('${upload:id}')
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
        $run = new WorkflowRun($this->workflow, $arguments->toArray());
        xdd($run);

        return new ResponseSuccess([]);
    }
}
