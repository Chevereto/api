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

namespace Chevereto\Actions\File;

use Chevere\Components\Action\Action;
use Chevere\Components\Parameter\IntegerParameter;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Parameter\StringParameter;
use Chevere\Components\Regex\Regex;
use Chevere\Components\Response\ResponseSuccess;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;

/**
 * Upload the filename to the target destination.
 */
class UploadAction extends Action
{
    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAddedRequired(
                new StringParameter('filename')
            )
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
                new IntegerParameter('storageId')
            )
            ->withAddedRequired(
                new StringParameter('uploadPath')
            );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        // uplodad filename to storage id

        return new ResponseSuccess(['id' => '123']);
    }
}
