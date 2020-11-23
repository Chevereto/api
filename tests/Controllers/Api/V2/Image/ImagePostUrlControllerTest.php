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

use Chevere\Components\Parameter\Arguments;
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Interfaces\Response\ResponseProvisionalInterface;
use Chevereto\Components\Enqueue;
use Chevereto\Components\Settings;
use Chevereto\Controllers\Api\V2\Image\ImagePostUrlController;
use PHPUnit\Framework\TestCase;
use function Safe\file_get_contents;

final class ImagePostUrlControllerTest extends TestCase
{
    public function testParameters(): void
    {
        $controller = new ImagePostUrlController;
        $sourceString = 'http://test.com';
        $source = $controller->getSourceParameter();
        $this->assertSame($sourceString, $source->regex()->match($sourceString)[0]);
    }

    public function testAssertStoreSource(): void
    {
        $source = 'https://1.1.1.1/';
        $path = __DIR__ . '/tmp';
        $controller = new ImagePostUrlController;
        $controller->assertStoreSource($source, $path);
        $this->assertStringEqualsFile($path, file_get_contents($source));
        unlink($path);
        $this->expectException(InvalidArgumentException::class);
        $controller->assertStoreSource('httpn://error', $path);
    }

    public function testRun(): void
    {
        $controller = new ImagePostUrlController;
        $controller = $controller
            ->withEnqueue(new Enqueue)
            // ->withWorkflow($controller->getWorkflow())
            ->withSettings(new Settings([
                'extensions' => 'jpg,png',
                'maxBytes' => '200000',
                'maxHeight' => '100',
                'maxWidth' => '100',
                'minBytes' => '1',
                'minHeight' => '1',
                'minWidth' => '1',
                'naming' => 'datefolder',
                'storageId' => '0',
                'uploadPath' => 'eeee',
                'userId' => '1',
                'ip' => '127.0.0.1',
                'ipVersion' => '4',
                'originalName' => 'laFotito.jpg',
                'expires' => '',
                'albumId' => ''
            ]));
        $arguments = new Arguments($controller->getParameters(), [
            'source' => 'https://1.1.1.1/'
        ]);
        $response = $controller->run($arguments);
        $this->assertInstanceOf(ResponseProvisionalInterface::class, $response);
    }
}
