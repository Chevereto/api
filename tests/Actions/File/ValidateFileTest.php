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

namespace Tests\Actions\File;

use Chevere\Components\Parameter\Arguments;
use Chevere\Components\Response\ResponseSuccess;
use Chevereto\Actions\File\ValidateAction;
use PHPUnit\Framework\TestCase;
use Tests\Actions\Traits\ExpectInvalidArgumentExceptionCodeTrait;

final class ValidateFileTest extends TestCase
{
    use ExpectInvalidArgumentExceptionCodeTrait;

    public function testConstruct(): void
    {
        $action = new ValidateAction;
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
                'bytes' => filesize(__FILE__),
                'mime' => 'text/x-php',
            ],
            $response->data()
        );
    }

    public function testMaxBytes(): void
    {
        $action = new ValidateAction;
        $parameters = [
            'filename' => __FILE__,
            'extensions' => 'php,txt',
            'maxBytes' => 20000000
        ];
        $arguments = new Arguments($action->parameters(), $parameters);
        $responseSuccess = $action->run($arguments);
        $this->assertInstanceOf(ResponseSuccess::class, $responseSuccess);
        $badArguments = new Arguments(
            $action->parameters(),
            array_merge($parameters, ['maxBytes' => 1])
        );
        $this->expectInvalidArgumentException(1100);
        $action->run($badArguments);
    }

    public function testMinBytes(): void
    {
        $action = new ValidateAction;
        $arguments = new Arguments(
            $action->parameters(),
            [
                'filename' => __FILE__,
                'extensions' => 'php',
                'minBytes' => 20000000
            ]
        );
        $this->expectInvalidArgumentException(1101);
        $action->run($arguments);
    }

    public function testExtension(): void
    {
        $action = new ValidateAction;
        $arguments = new Arguments(
            $action->parameters(),
            [
                'filename' => __FILE__,
                'extensions' => 'txt',
            ]
        );
        $this->expectInvalidArgumentException(1103);
        $action->run($arguments);
    }
}
