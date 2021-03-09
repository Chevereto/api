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

use Chevere\Components\Controller\ControllerWorkflow;
use Chevere\Components\Parameter\Parameters;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Parameter\StringParameterInterface;

abstract class FilePostController extends ControllerWorkflow
{
    abstract public function assertStoreSource(string $source, string $uploadFile): void;

    abstract public function getSourceParameter(): StringParameterInterface;

    public function getParameters(): ParametersInterface
    {
        return new Parameters(
            source: $this->getSourceParameter()
        );
    }
}
