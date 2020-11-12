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

namespace Tests\Controllers\Api\V1\Upload;

use Chevere\Exceptions\Core\OutOfBoundsException;
use Chevere\Interfaces\Workflow\WorkflowInterface;
use Chevereto\Components\Settings;
use Chevereto\Controllers\Api\V1\Upload\UploadPostController;
use PHPUnit\Framework\TestCase;

final class UploadPostControllerTest extends TestCase
{
    public function testConstruct(): void
    {
        $controller = new UploadPostController;
        $serviceProviders = $controller->getServiceProviders();
        $this->assertSame('withSettings', $serviceProviders->getGenerator()->key());
        $this->expectException(OutOfBoundsException::class);
        $controller = $controller->withSettings(new Settings([]));
    }

    public function testWithSettings(): void
    {
        $settings = new Settings([
            'apiV1Key' => 'api-key-value',
            'extensions' => 'php',
            'maxBytes' => '20000000',
            'maxHeight' => '20000',
            'maxWidth' => '20000',
            'minBytes' => '0',
            'minHeight' => '20',
            'minWidth' => '20',
            'naming' => 'original',
            'storageId' => '123',
            'uploadPath' => '2020/10/23',
            'userId' => '123',
        ]);
        $controller = (new UploadPostController)->withSettings($settings);
        $this->assertSame($settings, $controller->settings());
    }

    public function testWorkflow(): void
    {
        $this->assertInstanceOf(
            WorkflowInterface::class,
            (new UploadPostController)->getWorkflow()
        );
    }

    // public function testConstruct(): void
    // {
    //     $this->assertIsString($controller->getDescription());
    //     $arguments = new Arguments(
    //         $controller->getParameters(),
    //         [
    //             // 'source' => 'string source',
    //             'source' => __FILE__,
    //             'key' => 'api-key-value',
    //             // 'format' => 'json' // auto-filled
    //         ]
    //     );
    //     $controller = $controller
    //         ->withSetUp()
    //         ->withSettings(
    //             (new Settings)
    //                 // ->withPut('extensions', 'jpg,png,gif,webp')
    //                 ->withPut('extensions', 'php')
    //                 ->withPut('maxWidth', '20000')
    //                 ->withPut('maxHeight', '20000')
    //                 ->withPut('maxBytes', '20000000')
    //                 ->withPut('minWidth', '20')
    //                 ->withPut('minHeight', '20')
    //                 ->withPut('minBytes', '0')
    //                 ->withPut('apiV1Key', 'api-key-value')
    //                 ->withPut('uploadPath', '2020/10/23')
    //                 ->withPut('naming', 'original')
    //                 ->withPut('storageId', '123')
    //                 ->withPut('userId', '123')
    //         );
    //     $response = $controller->run($arguments);
    //     $this->assertInstanceOf(ResponseSuccessInterface::class, $response);
    //     $this->assertSame(json_encode(['id' => '123'], JSON_PRETTY_PRINT), $response->data()['raw']);
    // }
}
