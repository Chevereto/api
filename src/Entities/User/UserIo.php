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

use Chevereto\Components\Database\Database;
use Chevereto\Components\Database\IoInterface;
use Doctrine\DBAL\ParameterType;

final class UserIo implements IoInterface
{
    private Database $database;

    private string $table = 'user';

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function sql()
    {
        return $this->database->getQueryBuilder()
            ->select('*')
            ->from('chv_users')
            ->where('user_id = :userId')
            ->setParameter('userId', 1, ParameterType::INTEGER)
            ->execute()
            ->fetchAssociative();
    }

    public function get(int $userId): array
    {
        return [];
    }

    public function delete(int $userId): void
    {
    }

    public function update(int $userId, array $values): void
    {
    }

    public function insert(array $values): int
    {
        return 0;
    }
}
