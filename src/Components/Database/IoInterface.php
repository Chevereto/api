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

    public function get(int $id): array;

    public function delete(int $id): void;

    public function update(int $id, array $values): void;

    public function insert(array $values): int;
}
