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

use Chevere\Components\Message\Message;
use Chevere\Exceptions\Core\OutOfBoundsException;
use Chevereto\Components\Database\Database;
use Chevereto\Components\Database\IoInterface;
use Doctrine\DBAL\ParameterType;

final class UserIo implements IoInterface
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function get(int $userId, string ...$columns): array
    {
        $args = empty($columns) ? ['*'] : $columns;
        $result = $this->database->getQueryBuilder()
            ->select(...$args)
            ->from('chv_users')
            ->where('user_id = :userId')
            ->setParameter('userId', $userId, ParameterType::INTEGER)
            ->execute()
            ->fetchAssociative();
        if ($result === false) {
            throw new OutOfBoundsException(
                message: (new Message('No user exists for id %id%'))
                    ->code('%id%', (string) $userId)
            );
        }

        return $result;
    }

    public function delete(int $userId): int
    {
        return $this->database->getQueryBuilder()
            ->delete('chv_users')
            ->where('user_id = :userId')
            ->setParameter('userId', $userId, ParameterType::INTEGER)
            ->execute();
    }

    public function update(int $userId, string ...$values): int
    {
        $result = $this->database->getQueryBuilder()
            ->update('chv_users');
        foreach ($values as $column => $value) {
            $column = (string) $column;
            $result
                ->set($column, ":${column}")
                ->setParameter($column, $value);
        }

        return $result
            ->where('user_id = :userId')
            ->setParameter('userId', $userId, ParameterType::INTEGER)
            ->execute();
    }

    public function insert(array $values): int
    {
        return 0;
    }
}
