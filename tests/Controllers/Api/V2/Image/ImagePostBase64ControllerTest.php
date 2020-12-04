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

namespace Chevereto\Tests\Controllers\Api\V2\Image;

use Chevereto\Controllers\Api\V2\Image\ImagePostBase64Controller;
use PHPUnit\Framework\TestCase;

final class ImagePostBase64ControllerTest extends TestCase
{
    public function testAssertStoreSource(): void
    {
        $source = 'dGVzdA==';
        $original = 'test';
        $path = __DIR__ . '/tmp';
        $controller = new ImagePostBase64Controller;
        $controller->assertStoreSource($source, $path);
        $this->assertStringEqualsFile($path, $original);
        unlink($path);
    }
}
