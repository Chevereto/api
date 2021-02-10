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

namespace Chevereto\Tests\Components\HashId;

use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevereto\Components\HashId\HashId;
use PHPUnit\Framework\TestCase;

final class HashIdTest extends TestCase
{
    public function testInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new HashId(' ');
    }

    public function testLengthException(): void
    {
        $hashId = new HashId('salt');
        $this->expectException(InvalidArgumentException::class);
        $hashId->withPadding(-1);
    }

    public function testEncodeDecode(): void
    {
        $salt = 'salto';
        $id = 100;
        $decoded = 'vJ';
        $hashId = new HashId($salt);
        $this->assertSame($decoded, $hashId->encode($id));
        $this->assertSame($id, $hashId->decode($decoded));
    }

    public function testEncodeDecodeWithPadding(): void
    {
        $salt = 'salto';
        $id = 100;
        $padding = 1313;
        $decoded = 'YgF';
        $hashId = (new HashId($salt))->withPadding($padding);
        $this->assertSame($decoded, $hashId->encode($id));
        $this->assertSame($id, $hashId->decode($decoded));
    }

    public function testCollisions(): void {
        $encoded = [];
        $hashId = new HashId('salt');
        for ($id = 0; $id <= 1; $id++) {
            $encode = $hashId->encode($id);
            $this->assertArrayNotHasKey($encode, $encoded);
            $encoded[$encode] = $id;
            $decode = $hashId->decode($encode);
            $this->assertSame($id, $decode);
        }
    }
}