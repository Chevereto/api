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

namespace Chevereto\Permissions\Conditions;

use Chevere\Components\Permission\Condition;

final class ConditionCanUseApp extends Condition
{
    public function getDescription(): string
    {
        return 'Determines if able to use the application.';
    }

    public function getDefault(): bool
    {
        return true;
    }
}
