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

namespace Chevereto\Controllers\Api\V2\Album;

use Chevere\Components\Action\Controller;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Parameter\StringParameter;
use Chevere\Components\Regex\Regex;
use Chevere\Components\Response\ResponseSuccess;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Chevere\Interfaces\Response\ResponseSuccessInterface;

final class AlbumGetController extends Controller
{
    public function getDescription(): string
    {
        return 'Get an album identified by its id.';
    }

    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAddedRequired(
                id: (new StringParameter)
                    ->withRegex(new Regex('/\d+/'))
            );
    }

    public function run(ArgumentsInterface $arguments): ResponseSuccessInterface
    {
        return $this->getResponseSuccess([]);
    }
}
