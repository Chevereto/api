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
use function Safe\md5_file;

final class ValidateFileTest extends TestCase
{
    use ExpectInvalidArgumentExceptionCodeTrait;

    public function testConstruct(): void
    {
        $action = new ValidateAction;
        $arguments = [
            'filename' => __FILE__,
            'extensions' => 'php',
        ];
        $response = $action->run($arguments);
        $this->assertSame(
            [
                'bytes' => filesize(__FILE__),
                'mime' => 'text/x-php',
                'md5' => md5_file(__FILE__),
            ],
            $response->data()
        );
    }

    public function testMinBytes(): void
    {
        $action = new ValidateAction;
        $arguments = [
            'filename' => __FILE__,
            'extensions' => 'php',
            'minBytes' => 20000000
        ];
        $this->expectInvalidArgumentException(1001);
        $action->run($arguments);
    }

    public function testMaxBytes(): void
    {
        $action = new ValidateAction;
        $arguments = [
            'filename' => __FILE__,
            'extensions' => 'php,txt',
            'maxBytes' => 20000000
        ];
        $responseSuccess = $action->run($arguments);
        $this->assertInstanceOf(ResponseSuccess::class, $responseSuccess);
        $badArguments = array_merge($arguments, ['maxBytes' => 1]);
        $this->expectInvalidArgumentException(1002);
        $action->run($badArguments);
    }

    public function testExtension(): void
    {
        $action = new ValidateAction;
        $arguments = [
            'filename' => __FILE__,
            'extensions' => 'txt',
        ];
        $this->expectInvalidArgumentException(1004);
        $action->run($arguments);
    }
}
