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

namespace Chevereto\Entities\User;

use Chevereto\Components\Database\EntitiesIo;

/**
 * Provides database I/O for multiple User entities.
 */
final class UsersIo extends EntitiesIo
{
    public function table(): string
    {
        return 'user';
    }
}
