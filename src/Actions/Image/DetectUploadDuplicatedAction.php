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
use Chevere\Components\Regex\Regex;
use Chevere\Components\Response\ResponseSuccess;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;

/**
 * Detects image duplication based in both perceptual and file hashing, against the uploading frequency.
 *
 * Provides a run method returning a `ResponseSuccess` with
 * data `[]`.
 */
class DetectUploadDuplicatedAction extends Action
{
    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAddedRequired(
                (new StringParameter('md5'))
                    ->withRegex(new Regex('/^[a-f0-9]{32}$/'))
            )
            ->withAddedRequired(
                (new StringParameter('perceptual'))
                    ->withRegex(new Regex('/^[0-9A-F]+$/i'))
            );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        xdd($arguments->toArray());

        return new ResponseSuccess([]);
    }
}
