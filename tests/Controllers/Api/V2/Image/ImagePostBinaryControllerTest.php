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
use Chevere\Exceptions\Core\LogicException;
use Chevereto\Controllers\Api\V2\Image\ImagePostBinaryController;
use PHPUnit\Framework\TestCase;
use function Safe\tempnam;

final class ImagePostBinaryControllerTest extends TestCase
{
    public function testConstruct(): void {
        $controller = new ImagePostBinaryController();
        $this->assertSame($controller->getWorkflow()->name(), ImagePostBinaryController::class);
    }

    public function testInvalidArgument(): void {
        $controller = new ImagePostBinaryController();
        $this->expectException(InvalidArgumentException::class);
        $controller->assertStoreSource('', __DIR__ . '/stor');
    }

    public function testLogicException(): void {
        $controller = new ImagePostBinaryController();
        $this->expectException(LogicException::class);
        $controller->assertStoreSource('a:0:{}', __DIR__ . '/stor');
    }

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
