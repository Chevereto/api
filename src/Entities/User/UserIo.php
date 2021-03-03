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

use Chevereto\Components\Database\EntityIo;

/**
 * Provides database I/O for the User entity.
 */
final class UserIo extends EntityIo
{
    public function table(): string
    {
        return 'user';
    }

    public function id(): string
    {
        return 'id';
    }
}
