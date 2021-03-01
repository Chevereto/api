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

namespace Chevereto\Actions\Database;

use Chevere\Components\Action\Action;
use Chevere\Components\Dependent\Dependencies;
use Chevere\Components\Dependent\Traits\DependentTrait;
use Chevere\Components\Parameter\IntegerParameter;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Parameter\StringParameter;
use Chevere\Interfaces\Dependent\DependenciesInterface;
use Chevere\Interfaces\Dependent\DependentInterface;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Chevereto\Components\Database\Database;

/**
 * Reserves a row in the database.
 *
 * Arguments:
 *
 * ```php
 * table: string,
 * ```
 *
 * Response:
 *
 * ```php
 * id: int,
 * ```
 */
class DatabaseReserveRowAction extends Action implements DependentInterface
{
    use DependentTrait;

    private Database $database;

    public function getDependencies(): DependenciesInterface
    {
        return new Dependencies(
            database: Database::class
        );
    }

    public function getParameters(): ParametersInterface
    {
        return new Parameters(
            table: new StringParameter()
        );
    }

    public function getResponseDataParameters(): ParametersInterface
    {
        return new Parameters(
            id: new IntegerParameter()
        );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        // $db->insert row
        return $this->getResponse(id: 123);
    }
}
