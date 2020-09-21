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

final class ConditionImageRemoveExif extends Condition
{
    public function getDescription(): string
    {
        return 'Determines if image EXIF information should be removed.';
    }

    public function getDefault(): bool
    {
        return false;
    }
}
