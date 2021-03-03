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
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\ParameterType;

/**
 * Provides database I/O probing functionality for the User entity.
 */
final class UserProbe
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function isValidUsername(string $username): bool
    {
        return true;
    }

    public function isExistentUsername(string $username): bool
    {
        $queryBuilder = $this->database->getQueryBuilder()
            ->select('1')
            ->from('user')
            ->where('username = :username')
            ->setParameter('username', $username, ParameterType::STRING)
            ->setMaxResults(1);
        /** @var Result $result */
        $result = $queryBuilder->execute();
        $fetch = $result->fetchOne();
        if ($fetch === false) {
            return false;
        }

        return true;
    }
}
