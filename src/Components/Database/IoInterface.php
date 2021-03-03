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

namespace Chevereto\Components\Database;

interface IoInterface
{
    public function __construct(Database $database);

    public function get(int $userId, string ...$columns): array;

    /**
     * @return int Number of deleted rows `0`, `1`.
     */
    public function delete(int $id): int;

    /**
     * @return int Number of updated rows.
     */
    public function update(int $id, string ...$values): int;

    /**
     * @return int Last inserted Id.
     */
    public function insert(string ...$values): int;
}
