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
use Chevere\Components\Dependent\Dependencies;
use Chevere\Components\Dependent\Traits\DependentTrait;
use Chevere\Components\Parameter\IntegerParameter;
use Chevere\Components\Parameter\ObjectParameter;
use Chevere\Components\Parameter\Parameters;
use Chevere\Interfaces\Dependent\DependenciesInterface;
use Chevere\Interfaces\Dependent\DependentInterface;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Chevereto\Components\Db;
use Chevereto\Components\User;

class UserGetAction extends Action implements DependentInterface
{
    use DependentTrait;

    private Db $db;

    public function getDependencies(): DependenciesInterface
    {
        return (new Dependencies())
            ->withPut(
                db: Db::class
            );
    }

    public function getParameters(): ParametersInterface
    {
        return (new Parameters())
            ->withAddedRequired(
                userId: new IntegerParameter()
            );
    }

    public function getResponseDataParameters(): ParametersInterface
    {
        return (new Parameters())
            ->withAddedRequired(
                user: new ObjectParameter(User::class)
            );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        return $this->getResponse(
            user: new User($arguments->getInteger('userId'))
        );
    }
}
