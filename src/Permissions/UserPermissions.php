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

namespace Chevereto\Permissions;

use Chevere\Components\Permission\Conditions;
use Chevere\Components\Permission\Enums;
use Chevere\Interfaces\Permission\ConditionsInterface;
use Chevere\Interfaces\Permission\EnumsInterface;
use Chevere\Interfaces\Permission\RangesInterface;
use Chevereto\Permissions\Conditions\ConditionCanUseApp;

final class UserPermissions
{
    private ConditionsInterface $conditions;

    private EnumsInterface $enums;

    private RangesInterface $ranges;

    public function withConditions(ConditionsInterface $conditions): UserPermissions
    {
        $new = clone $this;
        $new->conditions = $conditions;

        return $new;
    }

    public function withEnums(EnumsInterface $enums): UserPermissions
    {
        $new = clone $this;
        $new->enums = $enums;

        return $new;
    }

    public function withRanges(RangesInterface $ranges): UserPermissions
    {
        $new = clone $this;
        $new->ranges = $ranges;

        return $new;
    }

    public function conditions(): ConditionsInterface
    {
        return $this->conditions;
    }

    public function enums(): EnumsInterface
    {
        return $this->enums;
    }

    public function ranges(): RangesInterface
    {
        return $this->ranges;
    }
}
 
$conditions = (new Conditions)->withAdded(new ConditionCanUseApp(true));
