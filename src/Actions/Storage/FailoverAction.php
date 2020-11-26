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
use Chevere\Components\Parameter\Parameters;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseSuccessInterface;

/**
 * Finds a valid storage to allocate the bytes required.
 */
class FailoverAction extends Action
{
    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAddedRequired(
                new IntegerParameter('userId')
            )
            ->withAddedRequired(
                new IntegerParameter('bytesRequired')
            );
    }

    public function getResponseDataParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAddedRequired(new IntegerParameter('storageId'));
    }

    public function run(array $arguments): ResponseSuccessInterface
    {
        $arguments = $this->getArguments($arguments);
        $userId = $arguments->getInteger('userId');
        $bytesRequired = $arguments->getInteger('bytesRequired');
        $storageId = 0;
        // user>
        // false> $storageId = $user->getStorageIdFor($bytesRequired);
        // $storage = new Storage($storageId)
        // $storage->canAllocate($bytesRequired);
        return $this->getResponseSuccess(['storageId' => $storageId]);
    }
}
