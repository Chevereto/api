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

use Chevere\Interfaces\Permission\ConditionsInterface;
use Chevere\Interfaces\Permission\EnumsInterface;
use Chevere\Interfaces\Permission\RangesInterface;

final class Permissions
{
    private ConditionsInterface $conditions;

    private EnumsInterface $enums;

    private RangesInterface $ranges;

    public function withConditions(ConditionsInterface $conditions): Permissions
    {
        $new = clone $this;
        $new->conditions = $conditions;

        return $new;
    }

    public function withEnums(EnumsInterface $enums): Permissions
    {
        $new = clone $this;
        $new->enums = $enums;

        return $new;
    }

    public function withRanges(RangesInterface $ranges): Permissions
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
