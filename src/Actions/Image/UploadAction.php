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
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Parameter\StringParameter;
use Chevere\Components\Regex\Regex;
use Chevere\Components\Response\ResponseSuccess;
use Chevere\Components\Service\ServiceProviders;
use Chevere\Components\Type\Type;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Chevere\Interfaces\Service\ServiceableInterface;
use Chevere\Interfaces\Service\ServiceProvidersInterface;
use Intervention\Image\Image;

/**
 * Upload the image to the target destination.
 *
 * Provides a run method returning a `ResponseSuccess` with
 * data `[]`.
 */
class UploadAction extends Action implements ServiceableInterface
{
    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAddedRequired(new Parameter('image', new Type(Image::class)))
            ->withAddedRequired(
                (new StringParameter('naming'))
                    ->withRegex(new Regex('/^(original|random|mixed|id)$/'))
                    ->withDefault('original')
            )
            ->withAddedRequired(
                (new StringParameter('originalName'))
                    ->withRegex(new Regex('/^.+\.[a-zA-Z]{3}$/'))
            )
            ->withAddedRequired(
                new Parameter('storageId', new Type(Type::INTEGER))
            )
            ->withAddedRequired(new StringParameter('uploadPath'));
    }

    public function getServiceProviders(): ServiceProvidersInterface
    {
        return (new ServiceProviders($this))
            ->withAdded('withLogger');
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        //? dummy row for 'id" filenaming (name the actual file just like the ID)
        // validate storage capacity, failover to *any if needed
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
