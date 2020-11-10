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

namespace Chevereto\Controllers\Api\V2\Image;

use Chevere\Components\Controller\Controller;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Parameter\StringParameter;
use Chevere\Components\Plugin\PluggableAnchors;
use Chevere\Components\Plugin\Plugs\Hooks\Traits\PluggableHooksTrait;
use Chevere\Components\Regex\Regex;
use Chevere\Components\Response\ResponseSuccess;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Plugin\PluggableAnchorsInterface;
use Chevere\Interfaces\Plugin\Plugs\Hooks\PluggableHooksInterface;
use Chevere\Interfaces\Response\ResponseInterface;

final class ImagePostController extends Controller implements PluggableHooksInterface
{
    use PluggableHooksTrait;

    public static function getHookAnchors(): PluggableAnchorsInterface
    {
        return (new PluggableAnchors)
            ->withAdded('setParameters')
            ->withAdded('setWorkflow');
    }

    public function getDescription(): string
    {
        return 'Uploads the attached resource.';
    }

    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAddedRequired(
                (new StringParameter('source'))->withRegex(new Regex('/.*/'))
            );
    }

    // public function setUp(): ControllerInterface
    // {
    //     $new = clone $this;
    //     $new->workflow = $this->getWorkflow();
    //     $new->hook('setParameters', $new->parameters);
    //     $new->hook('setWorkflow', $new->workflow);

    //     return $new;
    // }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        return new ResponseSuccess([]);
    }
}
