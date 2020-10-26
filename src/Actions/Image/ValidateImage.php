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
use Chevere\Components\Message\Message;
use Chevere\Components\Parameter\ParameterRequired;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Regex\Regex;
use Chevere\Components\Response\ResponseFailure;
use Chevere\Components\Response\ResponseSuccess;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Throwable;
use function Chevere\Components\Filesystem\fileForString;

class ValidateImage extends Action
{
    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAdded(
                (new ParameterRequired('filename'))
                    ->withRegex(new Regex('/^.+$/'))
            )
            ->withAdded(
                (new ParameterRequired('maxWidth'))
                    ->withRegex(new Regex('/^\d+$/'))
            )
            ->withAdded(
                (new ParameterRequired('maxHeight'))
                    ->withRegex(new Regex('/^\d+$/'))
            )
            ->withAdded(
                (new ParameterRequired('minWidth'))
                    ->withRegex(new Regex('/^\d+$/'))
                    ->withDefault('0')
            )
            ->withAdded(
                (new ParameterRequired('minHeight'))
                    ->withRegex(new Regex('/^\d+$/'))
                    ->withDefault('0')
            );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        try {
            $file = fileForString($arguments->get('filename'));
        } catch (Throwable $e) {
            return new ResponseFailure(
                [
                    'message' => (new Message('%message% for file at %path%'))
                        ->strong('%path%', $file->path()->absolute())
                        ->strtr('%message%', $e->getMessage())
                        ->toString(),
                    'code' => $e->getCode()
                ]
            );
        }

        return new ResponseSuccess([]);
    }
}
