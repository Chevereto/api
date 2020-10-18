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
use Chevere\Components\Parameter\Parameter;
use Chevere\Components\Parameter\ParameterOptional;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Response\ResponseSuccess;
use Chevere\Components\Service\ServiceProviders;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Chevere\Interfaces\Service\ServiceableInterface;
use Chevere\Interfaces\Service\ServiceProvidersInterface;
use Psr\Log\LoggerInterface;

class UploadImage extends Action implements ServiceableInterface
{
    private LoggerInterface $logger;

    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
        ->withAdded(new Parameter('filename'))
        ->withAdded(new ParameterOptional('userId'));
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
        /** UploadToWebsite */
        // user
        // check duplicates (md5, perceptual hash)
        // pick storage id
        // pick upload path
        // pick filenaming
        // pick default options for Upload
        // dummy row for 'id" filenaming (name the actual file just like the ID)
        // validate min/max stuff
        // autoresize large images
        // generate medium + thumb -> removed, use image server on-the-fly
        // validate storage capacity, failover to *any if needed
        // determine db image insert values
        // pick expirable uploads
        // upload to external storage -> to any* storage
        // inject db values (from exif and so on)
        // private upload triggers create private album
        // bind to album
        // bind to user

        /** Upload **/
        // destination
        // converts bmp to png
        // options [max_size, filenaming, exif, allowed formats,]
        // storageId
        // filename
        // handle flood -> not here
        // watermarks -> removed, use image server on-the-fly
        // fetch exif
        // clean exif from actual file
        // fix exif orientation
        // upload to storage (local, remote, whatever)

        // $this->logger->log('0', 'feeling good');
        // $filename = $arguments->get('filename');
        // where to?

        return new ResponseSuccess(['id' => '123']);
    }
}
