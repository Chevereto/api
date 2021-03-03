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
use Chevereto\Entities\User\User;
use Chevereto\Entities\User\UserIo;
use Chevereto\Entities\User\UserProbe;
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
        // $userIo = new UserIo($database);
        // xdd(
        //     (new UserProbe($database))->isExistentUsername('rodolfo')
        // );
        // $inserted = $userIo->insert(
        //     datetime_utc: '2021-03-03 15:27:00',
        //     name: 'Rodolfo',
        //     username: 'rodolfo',
        //     email: 'rodolfo@chevereto.com',
        //     website: 'https://rodolfoberrios.com',
        //     bio: 'El Rodo',
        //     timezone: '0',
        //     language: '0',
        //     status: '0',
        //     public_images: '0',
        //     public_videos: '0',
        //     public_audios: '0',
        //     followers: '0',
        //     following: '0',
        //     likes_given: '0',
        //     likes_made: '0',
        // );
        // $raw = $userIo->get(9, '*');
        // xdd(
        //     get: $raw,
        //     user: new User(...$raw)
        //     // update: $userIo->update($inserted, likes_given: '222'),
        //     // getUpdated: $userIo->get($inserted, 'likes_given'),
        //     // delete: $userIo->delete($inserted)
        // );
    }
}
