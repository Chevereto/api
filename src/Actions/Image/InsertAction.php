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
use Chevere\Components\Message\Message;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Parameter\StringParameter;
use Chevere\Components\Response\ResponseSuccess;
use Chevere\Components\Service\Traits\AssertDependenciesTrait;
use Chevere\Exceptions\Core\LogicException;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Chevere\Interfaces\Service\ServiceDependantInterface;
use DateTime;
use PDO;

/**
 * Insert the image in the database.
 *
 * Provides a run method returning a `ResponseSuccess` with
 * data `[]`.
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

    public function getDependencies(): ClassMap
    {
        return (new ClassMap)
            ->withPut(DateTime::class, 'dateTime');
    }

    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAddedOptional(
                (new StringParameter('expires'))
            )
            ->withAddedOptional(
                (new StringParameter('userId'))
            )
            ->withAddedOptional(
                (new StringParameter('albumId'))
            );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $this->assertDependencies();
        // determine db image insert values
        return new ResponseSuccess([]);
    }
}
