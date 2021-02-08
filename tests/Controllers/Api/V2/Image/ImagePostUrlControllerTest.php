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
use Chevere\Interfaces\Response\ResponseInterface;
use Chevereto\Controllers\Api\V2\Image\ImagePostUrlController;
use PHPUnit\Framework\TestCase;
use function Safe\file_get_contents;

final class ImagePostUrlControllerTest extends TestCase
{
    public function testParameters(): void
    {
        $controller = new ImagePostUrlController();
        $sourceString = 'http://test.com';
        $source = $controller->getSourceParameter();
        $this->assertSame($sourceString, $source->regex()->match($sourceString)[0]);
    }

    public function testAssertStoreSource(): void
    {
        $source = 'https://1.1.1.1/';
        $path = __DIR__ . '/tmp';
        $controller = new ImagePostUrlController();
        $controller->assertStoreSource($source, $path);
        $this->assertStringEqualsFile($path, file_get_contents($source));
        unlink($path);
        $this->expectException(InvalidArgumentException::class);
        $controller->assertStoreSource('httpn://error', $path);
    }

    public function testRun(): void
    {
        $context = [
            'mimes' => 'image/jpeg,image/png',
            'maxBytes' => 200000,
            'maxHeight' => 100,
            'maxWidth' => 100,
            'minBytes' => 1,
            'minHeight' => 1,
            'minWidth' => 1,
            'naming' => 'original',
            'path' => 'ee/ee',
            'userId' => 1,
            'ip' => '127.0.0.1',
            'ipVersion' => '4',
            'name' => 'laFotito.jpg',
            'expires' => 0,
            'albumId' => 0,
            'table' => 'images',
        ];
        $controller = (new ImagePostUrlController())->withContextArguments(...$context);
        $arguments = [
            'source' => 'https://1.1.1.1/',
        ];
        $response = $controller->run(
            $controller->getArguments(...$arguments)
        );
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}
