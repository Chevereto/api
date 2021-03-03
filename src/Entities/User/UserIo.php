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
use Chevereto\Components\Database\EntityIoInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Result;

/**
 * Provides database I/O for the User entity.
 */
final class UserIo implements EntityIoInterface
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function get(int $id, string ...$columns): array
    {
        $args = empty($columns) ? ['*'] : $columns;
        $queryBuilder = $this->database->getQueryBuilder()
            ->select(...$args)
            ->from('user')
            ->where('id = :id')
            ->setParameter('id', $id, ParameterType::INTEGER);
        /** @var Result $result */
        $result = $queryBuilder->execute();
        $fetch = $result->fetchAssociative();
        if ($fetch === false) {
            throw new OutOfBoundsException(
                message: (new Message('No user exists for id %id%'))
                    ->code('%id%', (string) $id)
            );
        }

        return $fetch;
    }

    public function delete(int $id): int
    {
        return $this->database->getQueryBuilder()
            ->delete('user')
            ->where('id = :id')
            ->setParameter('id', $id, ParameterType::INTEGER)
            ->execute();
    }

    public function update(int $id, string ...$values): int
    {
        $queryBuilder = $this->database->getQueryBuilder()
            ->update('user');
        foreach ($values as $column => $value) {
            $column = (string) $column;
            $queryBuilder
                ->set($column, ":${column}")
                ->setParameter($column, $value);
        }

        return $queryBuilder
            ->where('id = :id')
            ->setParameter('id', $id, ParameterType::INTEGER)
            ->execute();
    }

    public function insert(string ...$values): int
    {
        $queryBuilder = $this->database->getQueryBuilder()
            ->insert('user');
        foreach ($values as $column => $value) {
            $column = (string) $column;
            $queryBuilder
                ->setValue($column, ":${column}")
                ->setParameter($column, $value);
        }
        $result = $queryBuilder->execute();
        if ($result === 1) {
            return (int) $queryBuilder->getConnection()->lastInsertId();
        }

        return 0;
    }
}
