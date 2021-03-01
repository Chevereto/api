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

namespace Chevereto\Components\DataBase\User;

use Chevereto\Components\Database\Database;

final class UserProbe
{
    private Database $database;

    private string $table = 'user';

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function isValidUsername(string $username)
    {
    }

    public function isExistentUsername(string $username)
    {
    }
}
