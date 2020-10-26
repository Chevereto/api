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

namespace Chevereto\Tests\Actions\File;

use Chevere\Components\Parameter\Arguments;
use Chevere\Components\Response\ResponseSuccess;
use Chevere\Interfaces\Response\ResponseFailureInterface;
use Chevereto\Actions\File\ValidateFile;
use PHPUnit\Framework\TestCase;

final class ValidateFileTest extends TestCase
{
    public function testConstruct(): void
    {
        $action = new ValidateFile;
        $arguments = new Arguments(
            $action->parameters(),
            [
                'filename' => __FILE__,
                'extensions' => 'php',
            ]
        );
        $response = $action->run($arguments);
        $this->assertSame(
            [
                'filename' => __FILE__,
                'bytes' => filesize(__FILE__),
                'mime' => 'text/x-php',
                'extension' => 'php'
            ],
            $response->data()
        );
    }

    public function testMinBytes(): void
    {
        $action = new ValidateFile;
        $arguments = new Arguments(
            $action->parameters(),
            [
                'filename' => __FILE__,
                'extensions' => 'php',
                'minBytes' => '20000000'
            ]
        );
        $response = $action->run($arguments);
        $this->assertInstanceOf(ResponseFailureInterface::class, $response);
        $this->assertSame(1100, $response->data()['code']);
    }

    public function testMaxBytes(): void
    {
        $action = new ValidateFile;
        $parameters = [
            'filename' => __FILE__,
            'extensions' => 'php,txt',
            'maxBytes' => '20000000'
        ];
        $arguments = new Arguments($action->parameters(), $parameters);
        $responseSuccess = $action->run($arguments);
        $this->assertInstanceOf(ResponseSuccess::class, $responseSuccess);
        $badArguments = new Arguments(
            $action->parameters(),
            array_merge($parameters, ['maxBytes' => '1'])
        );
        $responseFailure = $action->run($badArguments);
        $this->assertInstanceOf(ResponseFailureInterface::class, $responseFailure);
        $this->assertSame(1101, $responseFailure->data()['code']);
    }

    public function testExtension(): void
    {
        $action = new ValidateFile;
        $arguments = new Arguments(
            $action->parameters(),
            [
                'filename' => __FILE__,
                'extensions' => 'txt',
            ]
        );
        $response = $action->run($arguments);
        $this->assertInstanceOf(ResponseFailureInterface::class, $response);
        $this->assertSame(1103, $response->data()['code']);
    }
}
