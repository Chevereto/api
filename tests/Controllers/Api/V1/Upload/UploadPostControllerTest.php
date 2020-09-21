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

namespace CheveretoTests\Controllers\Api\V1\Upload;

use Chevere\Components\Parameter\Arguments;
use Chevere\Interfaces\Response\ResponseSuccessInterface;
use Chevereto\Controllers\Api\V1\Upload\UploadPostController;
use PHPUnit\Framework\TestCase;

final class UploadPostControllerTest extends TestCase
{
    public function testConstruct(): void
    {
        $controller = new UploadPostController;
        $this->assertIsString($controller->getDescription());
        $arguments = new Arguments(
            $controller->getParameters(),
            [
                'source' => 'string source',
                'key' => 'some key',
                'format' => 'json'
            ]
        );
        $controller = $controller->setUp();
        $response = $controller->run($arguments);
        $this->assertInstanceOf(ResponseSuccessInterface::class, $response);
        $this->assertSame('123', $response->data()['id']);
    }
}
