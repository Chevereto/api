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

namespace Chevereto\Actions\Legacy;

use Chevere\Components\Action\Action;
use Chevere\Components\Dependent\Dependencies;
use Chevere\Components\Dependent\Traits\DependentTrait;
use Chevere\Components\Message\Message;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Parameter\StringParameter;
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Interfaces\Dependent\DependenciesInterface;
use Chevere\Interfaces\Dependent\DependentInterface;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;

class ValidateApiV1KeyAction extends Action implements DependentInterface
{
    use DependentTrait;

    private $settings;

    // public function getDependencies(): DependenciesInterface
    // {
    //     return new Dependencies(
    //         settings: Settings::class
    //     );
    // }

    public function getParameters(): ParametersInterface
    {
        return new Parameters(
            key: new StringParameter(),
        );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $this->assertDependencies();
        // $apiV1Key = $this->settings->get('apiV1Key');
        $apiV1Key = 'test';
        if ($arguments->getString('key') !== $apiV1Key) {
            throw new InvalidArgumentException(
                message: new Message('Invalid API V1 key provided'),
                code: 100
            );
        }

        return $this->getResponse();
    }
}
