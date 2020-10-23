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
use Chevere\Components\Parameter\ParameterRequired;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Response\ResponseSuccess;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;

class ValidateImage extends Action
{
    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAdded(new ParameterRequired('filename'))
            ->withAdded(new ParameterRequired('extensions'))
            ->withAdded(new ParameterRequired('maxWidth'))
            ->withAdded(new ParameterRequired('maxHeight'))
            ->withAdded(new ParameterRequired('maxBytes'))
            ->withAdded(new ParameterRequired('minWidth'))
            ->withAdded(new ParameterRequired('minHeight'))
            ->withAdded(new ParameterRequired('minBytes'));
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $image = $arguments->get('filename');
        // validate integrity
        // get real extension - fix it
        return new ResponseSuccess([]);
    }
}
