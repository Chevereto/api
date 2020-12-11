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

namespace Chevereto\Actions\User;

use Chevere\Components\Action\Action;
use Chevere\Components\Parameter\IntegerParameter;
use Chevere\Components\Parameter\Parameter;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Service\Traits\ServiceDependantTrait;
use Chevere\Components\Type\Type;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseSuccessInterface;
use Chevere\Interfaces\Service\ServiceDependantInterface;
use Chevereto\Components\User;

class UserGetAction extends Action implements ServiceDependantInterface
{
    use ServiceDependantTrait;

    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAddedRequired(
                userId: new IntegerParameter
            );
    }

    public function getResponseDataParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAddedRequired(
                user: new Parameter(new Type(User::class))
            );
    }

    public function run(ArgumentsInterface $arguments): ResponseSuccessInterface
    {
        return $this->getResponseSuccess([
            'user' => new User($arguments->getInteger('userId')),
        ]);
    }
}
