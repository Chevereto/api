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

namespace Chevereto\Vendor\rodolfoberrios\SepiaFilter\Permissions\Conditions;

use Chevereto\Permissions\Conditions\Condition;

final class ConditionApply extends Condition
{
    public function getDescription(): string
    {
        return 'Determines if able to use the sepia filter.';
    }

    public function getDefault(): bool
    {
        return true;
    }
}
