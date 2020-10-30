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

namespace Chevereto\Actions\Image;

use Chevere\Components\Action\Action;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Parameter\StringParameter;
use Chevere\Components\Response\ResponseSuccess;
use Chevere\Components\Service\ServiceProviders;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Chevere\Interfaces\Service\ServiceableInterface;
use Chevere\Interfaces\Service\ServiceProvidersInterface;
use Psr\Log\LoggerInterface;

/**
 * Upload the image to the target destination.
 *
 * Provides a run method returning a `ResponseSuccess` with
 * data `[]`.
 */
class UploadAction extends Action implements ServiceableInterface
{
    private LoggerInterface $logger;

    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAddedRequired(
                (new StringParameter('expires'))
            )
            ->withAddedRequired(new StringParameter('filename'))
            ->withAddedRequired(new StringParameter('uploadPath'))
            ->withAddedRequired(new StringParameter('naming'))
            ->withAddedRequired(new StringParameter('storageId'))
            ->withAddedRequired(new StringParameter('userId'))
            ->withAddedRequired(new StringParameter('albumId'));
    }

    public function getServiceProviders(): ServiceProvidersInterface
    {
        return (new ServiceProviders($this))
            ->withAdded('withUploader')
            ->withAdded('withLogger');
    }

    public function withLogger(LoggerInterface $logger): self
    {
        $new = clone $this;
        $new->logger = $logger;

        return $new;
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        //? dummy row for 'id" filenaming (name the actual file just like the ID)
        // validate storage capacity, failover to *any if needed
        // determine db image insert values
        // upload to external storage -> to any* storage
        // inject db values (from exif and so on)
        // bind to album
        // bind to user

        /** Upload **/
        // handle flood -> not here
        // upload to storage (local, remote, whatever)

        return new ResponseSuccess(['id' => '123']);
    }
}
