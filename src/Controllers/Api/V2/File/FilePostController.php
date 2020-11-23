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

namespace Chevereto\Controllers\Api\V2\File;

use Chevere\Components\Parameter\Parameters;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Parameter\StringParameterInterface;
use Chevere\Interfaces\Workflow\TaskInterface;
use Chevereto\Controllers\Api\V2\File\Traits\FileStorageFailoverTaskTrait;
use Chevereto\Controllers\Api\V2\File\Traits\FileValidateFileTaskTrait;
use Chevereto\Controllers\Api\V2\QueueController;

abstract class FilePostController extends QueueController
{
    use FileValidateFileTaskTrait, FileStorageFailoverTaskTrait;

    abstract public function assertStoreSource(string $source, string $uploadFile): void;

    abstract public function getSourceParameter(): StringParameterInterface;

    abstract public function getValidateMediaTask(): TaskInterface;

    public function getParameters(): ParametersInterface
    {
        $source = $this->getSourceParameter();

        return (new Parameters)
            ->withAddedRequired($source);
    }
}
