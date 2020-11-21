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
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;

/**
 * Insert the image in the database.
 *
 * Provides a run method returning a `ResponseSuccess` with
 * data `[]`.
 */
class InsertAction extends Action
{
    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAddedOptional(
                (new StringParameter('expires'))
            )
            ->withAddedOptional(
                (new StringParameter('userId'))
            )
            ->withAddedOptional(
                (new StringParameter('albumId'))
            );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        // determine db image insert values
        return new ResponseSuccess([]);
    }
}
