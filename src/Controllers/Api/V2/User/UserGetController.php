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

namespace Chevereto\Controllers\Api\V2\User;

use Chevere\Components\Controller\Controller;
use Chevere\Components\Parameter\Parameter;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Regex\Regex;
use Chevere\Components\Response\ResponseSuccess;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;

final class UserGetController extends Controller
{
    public function getDescription(): string
    {
        return 'Get an user identified by its id.';
    }

    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAdded(
                (new Parameter('id', new Regex('/\d+/')))
                    ->withDescription('The user identifier.')
            );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $id = $arguments->get('id');

        return new ResponseSuccess(['id' => $id, ]);
    }
}
