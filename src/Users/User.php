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

namespace Chevereto\Users;

use Chevereto\Permissions\UserPermissions;

final class User
{
    private UserPermissions $permissions;

    public function __construct(UserPermissions $permissions)
    {
        $this->permissions = $permissions;
    }

    public function permissions(): UserPermissions
    {
        return $this->permissions;
    }
}
