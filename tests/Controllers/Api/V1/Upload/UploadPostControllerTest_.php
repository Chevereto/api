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

use Chevere\Components\Parameter\Arguments;
use Chevere\Interfaces\Response\ResponseSuccessInterface;
use Chevereto\Components\Settings;
use Chevereto\Components\User;
use Chevereto\Controllers\Api\V1\Upload\ImagePostController;
use PHPUnit\Framework\TestCase;
use function Safe\json_encode;

final class ImagePostControllerTest extends TestCase
{
    // public function testConstruct(): void
    // {
    //     $controller = new ImagePostController;
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
