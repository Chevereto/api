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
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseSuccessInterface;

final class AlbumPostController extends Controller
{
    public function getDescription(): string
    {
        return 'Creates an album.';
    }

    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAddedRequired(
                (new StringParameter('name'))
                    ->withRegex(new Regex('/\w+/')),
            );
    }

    public function run(array $arguments): ResponseSuccessInterface
    {
        return $this->getResponseSuccess([]);
    }
}
