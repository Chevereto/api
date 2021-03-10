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

namespace Chevereto\Actions\Account\Asset;

use Chevere\Components\Action\Action;
use Chevere\Components\Filesystem\Filename;
use Chevere\Components\Filesystem\Path;
use Chevere\Components\Parameter\ObjectParameter;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Parameter\StringParameter;
use Chevere\Components\Regex\Regex;
use function Chevere\Components\Str\randomString;
use Chevere\Interfaces\Filesystem\PathInterface;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;

class AccountAssetAction extends Action
{
    public function getParameters(): ParametersInterface
    {
        return new Parameters(
            format: (new StringParameter())
                ->withRegex(new Regex('/^jpe?g|webp|gif|png$/')),
            path: new StringParameter(),
        );
    }

    public function getResponseParameters(): ParametersInterface
    {
        return new Parameters(
            filename: new ObjectParameter(Filename::class),
            path: new ObjectParameter(PathInterface::class),
        );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $filename = randomString(32);
        $format = $arguments->getString('format');
        $path = $arguments->getString('path');

        return $this->getResponse(
            filename: new Filename("${filename}.${format}"),
            path: new Path($path),
        );
    }
}
