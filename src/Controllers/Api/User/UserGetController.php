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

namespace Chevereto\Controllers\Api\User;

use Chevere\Components\Controller\Controller;
use Chevere\Components\Controller\ControllerParameter;
use Chevere\Components\Controller\ControllerParameters;
use Chevere\Components\Controller\ControllerResponse;
use Chevere\Components\Regex\Regex;
use Chevere\Interfaces\Controller\ControllerArgumentsInterface;
use Chevere\Interfaces\Controller\ControllerParametersInterface;
use Chevere\Interfaces\Controller\ControllerResponseInterface;

final class UserGetController extends Controller
{
    public function getDescription(): string
    {
        return 'Get an user identified by its id.';
    }

    public function getParameters(): ControllerParametersInterface
    {
        return (new ControllerParameters)
            ->withAdded(
                (new ControllerParameter('id', new Regex('/\d+/')))
                    ->withDescription('The user identifier.')
            );
    }

    public function run(ControllerArgumentsInterface $controllerArguments): ControllerResponseInterface
    {
        $id = $controllerArguments->get('id');

        return new ControllerResponse(true, [
            'id' => $id,
        ]);
    }
}
