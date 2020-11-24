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
use Chevere\Components\ClassMap\ClassMap;
use Chevere\Components\Parameter\IntegerParameter;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Response\ResponseSuccess;
use Chevere\Components\Service\Traits\AssertDependenciesTrait;
use Chevere\Interfaces\ClassMap\ClassMapInterface;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Chevere\Interfaces\Service\ServiceDependantInterface;
use DateTime;

/**
 * Insert the image in the database.
 */
class InsertAction extends Action implements ServiceDependantInterface
{
    use AssertDependenciesTrait;

    private DateTime $dateTime;

    public function withDependencies(array $namedArguments): self
    {
        $new = clone $this;
        $new->dateTime = $namedArguments['dateTime'];

        return $new;
    }

    public function getDependencies(): ClassMapInterface
    {
        return (new ClassMap)
            ->withPut(DateTime::class, 'dateTime');
    }

    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAddedOptional(
                new IntegerParameter('expires')
            )
            ->withAddedOptional(
                new IntegerParameter('userId')
            )
            ->withAddedOptional(
                new IntegerParameter('albumId')
            );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $this->assertDependencies();
        // TODO: DB inserting
        return new ResponseSuccess([]);
    }
}
