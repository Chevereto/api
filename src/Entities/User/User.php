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

final class User
{
    private int $id;

    public function __construct(array $properties)
    {
        $this->id = $properties['id'] ?? 0;
    }

    public function id(): int
    {
        return $this->id;
    }

    // public function getUrl($handle) {
    // }
    // public function getUrlAlbums($user_url) {
    // }
}
