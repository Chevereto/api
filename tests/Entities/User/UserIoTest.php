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

namespace Chevereto\Tests\Entities\User;

use Chevereto\Components\Database\Database;
use Chevereto\Entities\User\UserIo;
use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\TestCase;

final class UserIoTest extends TestCase
{
    public function testWea(): void
    {
        $this->expectNotToPerformAssertions();
        $connectionParams = [
            'dbname' => 'chevereto-4',
            'user' => 'root',
            'password' => 'root',
            'host' => 'localhost',
            'driver' => 'pdo_mysql',
        ];
        $connection = DriverManager::getConnection($connectionParams);
        $database = new Database($connection);
        $userIo = new UserIo($database);
        // xdd($userIo->sql());
    }
}
