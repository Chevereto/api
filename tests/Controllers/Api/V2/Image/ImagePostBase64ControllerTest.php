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
    // public function testConstruct(): void
    // {
    //     $controller = new ImagePostBase64Controller;
    //     $serviceProviders = $controller->getServiceProviders();
    //     $this->assertSame('withSettings', $serviceProviders->getGenerator()->key());
    //     $this->expectException(OutOfBoundsException::class);
    //     $controller = $controller->withSettings(new Settings([]));
    // }

    // public function testWithSettings(): void
    // {
    //     $settings = new Settings([
    //         'extensions' => 'php',
    //         'maxBytes' => '20000000',
    //         'maxHeight' => '20000',
    //         'maxWidth' => '20000',
    //         'minBytes' => '0',
    //         'minHeight' => '20',
    //         'minWidth' => '20',
    //         'naming' => 'original',
    //         'storageId' => '123',
    //         'uploadPath' => '2020/10/23',
    //         'userId' => '123',
    //     ]);
    //     $controller = (new ImagePostBase64Controller)->withSettings($settings);
    //     $this->assertSame($settings, $controller->settings());
    // }

    // public function testWorkflow(): void
    // {
    //     // $this->assertInstanceOf(
    //     //     WorkflowInterface::class,
    //     //     (new ImagePostBase64Controller)->getWorkflow()
    //     // );
    // }

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
