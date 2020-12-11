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
use Chevere\Components\Parameter\IntegerParameter;
use Chevere\Components\Parameter\Parameter;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Type\Type;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseSuccessInterface;
use Chevereto\Components\Storage;

/**
 * Finds a valid storage to allocate the bytes required.
 */
class StorageGetForUserAction extends Action
{
    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAddedRequired(
                userId: new IntegerParameter,
                bytesRequired: new IntegerParameter,
            );
    }

    public function getResponseDataParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAddedRequired(
                storage: new Parameter(new Type(Storage::class)),
            );
    }

    public function run(ArgumentsInterface $arguments): ResponseSuccessInterface
    {
        $userId = $arguments->getInteger('userId');
        $bytesRequired = $arguments->getInteger('bytesRequired');
        // $storage = $service->getStorageFor($userId, $bytesRequired)

        return $this->getResponseSuccess(['storage' => new Storage(0)]);
    }
}
