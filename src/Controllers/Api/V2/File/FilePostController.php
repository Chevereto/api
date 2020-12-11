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

use Chevere\Components\Parameter\IntegerParameter;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Parameter\StringParameter;
use Chevere\Components\Regex\Regex;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Parameter\StringParameterInterface;
use Chevereto\Controllers\Api\V2\QueueController;

abstract class FilePostController extends QueueController
{
    abstract public function assertStoreSource(string $source, string $uploadFile): void;

    abstract public function getSourceParameter(): StringParameterInterface;

    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAddedRequired(source: $this->getSourceParameter());
    }

    public function getContextParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAddedRequired(
                expires: new IntegerParameter,
                maxBytes: new IntegerParameter,
                minBytes: new IntegerParameter,
                userId: new IntegerParameter,
                extensions: new StringParameter,
                ip: new StringParameter,
                naming: new StringParameter,
                originalName: new StringParameter,
                uploadPath: new StringParameter,
                ipVersion: (new StringParameter)
                    ->withRegex(new Regex('/^[4|6]$/')),
            );
    }
}
