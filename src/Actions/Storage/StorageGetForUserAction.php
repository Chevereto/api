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

namespace Chevereto\Actions\Storage;

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
use Chevereto\Components\Database\Database;
use Chevereto\Components\Storage\Storage;
use League\Flysystem\Local\LocalFilesystemAdapter;

/**
 * Finds a valid storage to allocate the bytes required.
 *
 * Response parameters:
 *
 * ```php
 * storage: \Chevereto\Interfaces\Storage\StorageInterface,
 * ```
 */
class StorageGetForUserAction extends Action implements DependentInterface
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
            userId: new IntegerParameter(),
            bytesRequired: new IntegerParameter(),
        );
    }

    public function getResponseParameters(): ParametersInterface
    {
        return new Parameters(
            storage: new ObjectParameter(Storage::class)
        );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $userId = $arguments->getInteger('userId');
        $bytesRequired = $arguments->getInteger('bytesRequired');
        // $adapter = db->query storage for user;
        $adapter = new LocalFilesystemAdapter(__DIR__);

        return $this->getResponse(storage: new Storage($adapter));
    }
}
