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

namespace Chevereto\Vendor\rodolfoberrios\ApiV1XMLFormat\Hooks;

use Chevere\Components\Parameter\StringParameter;
use Chevere\Components\Regex\Regex;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Plugin\Plugs\Hooks\HookInterface;
use Chevereto\Controllers\Api\V1\Upload\UploadPostController;

final class SetParametersHook implements HookInterface
{
    /**
     * @param ParametersInterface $parameters
     */
    public function __invoke(&$parameters): void
    {
        /**
         * @var StringParameter $parameter
         */
        $parameter = $parameters->get('format');
        $parameters = $parameters->withModify(
            $parameter->withRegex(new Regex('/^(json|redirect|txt|xml)$/'))
        );
    }

    public function anchor(): string
    {
        return 'setParameters';
    }

    public function at(): string
    {
        return UploadPostController::class;
    }

    public function priority(): int
    {
        return 0;
    }
}
