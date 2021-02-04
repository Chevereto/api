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

use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevereto\Controllers\Api\V2\Image\ImagePostBinaryController;
use PHPUnit\Framework\TestCase;
use function Safe\tempnam;

final class ImagePostBinaryControllerTest extends TestCase
{
    public function testAssertStoreSource(): void
    {
        $files = [
            'tmp_name' => __FILE__,
        ];
        $source = serialize($files);
        $path = tempnam(__DIR__, 'chv');
        $controller = new ImagePostBinaryController();
        $controller->assertStoreSource($source, $path);
        $this->assertStringEqualsFile($path, file_get_contents(__FILE__));
        unlink($path);
        $this->expectException(InvalidArgumentException::class);
        $controller->assertStoreSource('error', $path);
    }
}
