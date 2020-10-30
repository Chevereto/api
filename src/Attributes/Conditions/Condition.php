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

namespace Chevereto\Attributes\Conditions;

use Chevere\Components\Attribute\Condition as BaseCondition;
use Chevere\Components\Str\StrBool;

abstract class Condition extends BaseCondition
{
    final public function getIdentifier(): string
    {
        $identifier = $this->getIdentifier();
        if ((new StrBool(static::class))->startsWith('Chevereto\\Vendor\\')) {
            $explode = explode('\\', static::class);
            $vendor = mb_strtolower($explode[2]);
            $package = mb_strtolower($explode[3]);
            $identifier = $vendor . '-' . $package . '_' . $identifier;
        }

        return $identifier;
    }
}
