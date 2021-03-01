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

use Chevere\Interfaces\Response\ResponseInterface;
use Chevereto\Actions\File\FileValidateAction;
use PHPUnit\Framework\TestCase;
use function Safe\md5_file;
use Tests\Actions\Traits\ExpectInvalidArgumentExceptionCodeTrait;

final class FileValidateActionTest extends TestCase
{
    use ExpectInvalidArgumentExceptionCodeTrait;

    public function testConstruct(): void
    {
        $action = new FileValidateAction();
        $arguments = [
            'filename' => __FILE__,
            'mimes' => 'text/x-php',
        ];
        $response = $action->run($action->getArguments(...$arguments));
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
        $action = new FileValidateAction();
        $arguments = [
            'filename' => __FILE__,
            'mimes' => 'text/x-php',
            'minBytes' => 20000000,
        ];
        $this->expectInvalidArgumentException(1001);
        $action->run($action->getArguments(...$arguments));
    }

    public function testMaxBytes(): void
    {
        $action = new FileValidateAction();
        $arguments = [
            'filename' => __FILE__,
            'mimes' => 'text/x-php,text/plain',
            'maxBytes' => 20000000,
            'minBytes' => 1,
        ];
        $response = $action->run($action->getArguments(...$arguments));
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $badArguments = array_merge($arguments, [
            'maxBytes' => 1,
        ]);
        $this->expectInvalidArgumentException(1002);
        $action->run($action->getArguments(...$badArguments));
    }

    public function testMime(): void
    {
        $action = new FileValidateAction();
        $arguments = [
            'filename' => __FILE__,
            'mimes' => 'text/plain,application/pdf',
        ];
        $this->expectInvalidArgumentException(1004);
        $action->run($action->getArguments(...$arguments));
    }
}
