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

final class UserAlbumDb
{
    private Database $database;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getAlbums(int $userId)
    {
    }

    public function getStreamAlbum(int $userId)
    {
    }

    public function getUrlAlbums(int $userId)
    {
    }
}
