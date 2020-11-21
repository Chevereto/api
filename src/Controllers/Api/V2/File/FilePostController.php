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
use Chevere\Components\Workflow\Task;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Parameter\StringParameterInterface;
use Chevere\Interfaces\Workflow\TaskInterface;
use Chevereto\Actions\File\DetectDuplicateAction;
use Chevereto\Actions\File\ValidateAction;
use Chevereto\Actions\Storage\FailoverAction;
use Chevereto\Controllers\Api\V2\QueueController;

abstract class FilePostController extends QueueController
{
    abstract public function assertStoreSource(string $source, string $uploadFile): void;

    abstract public function getSourceParameter(): StringParameterInterface;

    abstract public function getValidateTask(): TaskInterface;

    public function getParameters(): ParametersInterface
    {
        $source = $this->getSourceParameter();

        return (new Parameters)
            ->withAddedRequired($source);
    }

    final public function getValidateFileTask(): TaskInterface
    {
        return (new Task(ValidateAction::class))
            ->withArguments([
                'extensions' => '${extensions}',
                'filename' => '${filename}',
                'maxBytes' => '${maxBytes}',
                'minBytes' => '${minBytes}',
            ]);
    }

    final public function getDetectDuplicateTask(): TaskInterface
    {
        return (new Task(DetectDuplicateAction::class))
            ->withArguments([
                'md5' => '${validate:md5}',
                'perceptual' => '${validate:perceptual}',
                'ipv4' => '${ipv4}',
                'ipv6' => '${ipv6}',
            ]);
    }

    final public function getStorageFailoverTask(): TaskInterface
    {
        return (new Task(FailoverAction::class))
            ->withArguments([
                'storageId' => 0
            ]);
    }
}
