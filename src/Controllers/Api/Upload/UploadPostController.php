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

namespace Chevereto\Controllers\Api\Upload;

use Chevere\Components\Controller\Controller;
use Chevere\Components\Controller\ControllerParameter;
use Chevere\Components\Controller\ControllerParameters;
use Chevere\Components\Controller\ControllerResponse;
use Chevere\Components\Regex\Regex;
use Chevere\Interfaces\Controller\ControllerArgumentsInterface;
use Chevere\Interfaces\Controller\ControllerParametersInterface;
use Chevere\Interfaces\Controller\ControllerResponseInterface;

final class UploadPostController extends Controller
{
    public function getDescription(): string
    {
        return 'Uploads the attached resource.';
    }

    public function getParameters(): ControllerParametersInterface
    {
        return (new ControllerParameters)
            ->withAdded(
                new ControllerParameter('source', new Regex('/.*/'))
            );
    }

    public function run(ControllerArgumentsInterface $controllerArguments): ControllerResponseInterface
    {
        return new ControllerResponse(true, []);
    }
}
