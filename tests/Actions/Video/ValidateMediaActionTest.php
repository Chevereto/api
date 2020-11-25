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

namespace Chevereto\Tests\Actions\Video;

use Chevere\Components\Parameter\Arguments;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Response\ResponseSuccessInterface;
use Chevereto\Actions\Video\ValidateMediaAction;
use FFMpeg\FFProbe\DataMapping\Format;
use FFMpeg\FFProbe\DataMapping\Stream;
use PHPUnit\Framework\TestCase;
use Tests\Actions\Traits\ExpectInvalidArgumentExceptionCodeTrait;

final class ValidateMediaActionTest extends TestCase
{
    use ExpectInvalidArgumentExceptionCodeTrait;

    /**
     * Arguments for a 560x320 5s video
     */
    private function getTestArguments(ValidateMediaAction $action, array $arguments): ArgumentsInterface
    {
        return new Arguments(
            $action->parameters(),
            array_replace([
                'filename' => __DIR__ . '/assets/small.webm',
                'maxHeight' => 20000,
                'maxWidth' => 20000,
                'maxLength' => 3600,
                'minHeight' => 0,
                'minWidth' => 0,
                'minLength' => 0,
            ], $arguments)
        );
    }

    public function testConstruct(): void
    {
        $action = new ValidateMediaAction;
        $arguments = $this->getTestArguments($action, []);
        $response = $action->run($arguments);
        $this->assertInstanceOf(ResponseSuccessInterface::class, $response);
        $this->assertInstanceOf(Format::class, $response->data()['format']);
        $this->assertInstanceOf(Stream::class, $response->data()['stream']);
    }

    public function testInvalidVideo(): void
    {
        $action = new ValidateMediaAction;
        $arguments = $this->getTestArguments($action, ['filename' => __FILE__]);
        $this->expectInvalidArgumentException(1000);
        $action->run($arguments);
    }

    public function testInvalidVideoImage(): void
    {
        $action = new ValidateMediaAction;
        $arguments = $this->getTestArguments(
            $action,
            ['filename' => __DIR__ . '/../Image/assets/favicon.png']
        );
        $this->expectInvalidArgumentException(1000);
        $action->run($arguments);
    }

    public function testInvalidVideoAudio(): void
    {
        $action = new ValidateMediaAction;
        $arguments = $this->getTestArguments(
            $action,
            ['filename' => __DIR__ . '/../Audio/assets/small.mp3']
        );
        $this->expectInvalidArgumentException(1000);
        $action->run($arguments);
    }

    public function testMinWidth(): void
    {
        $action = new ValidateMediaAction;

        $arguments = $this->getTestArguments(
            $action,
            ['minWidth' => 561]
        );
        $this->expectInvalidArgumentException(1001);
        $action->run($arguments);
    }

    public function testMaxWidth(): void
    {
        $action = new ValidateMediaAction;

        $arguments = $this->getTestArguments(
            $action,
            ['maxWidth' => 559]
        );
        $this->expectInvalidArgumentException(1002);
        $action->run($arguments);
    }

    public function testMinHeight(): void
    {
        $action = new ValidateMediaAction;

        $arguments = $this->getTestArguments(
            $action,
            ['minHeight' => 321]
        );
        $this->expectInvalidArgumentException(1003);
        $action->run($arguments);
    }

    public function testMaxHeight(): void
    {
        $action = new ValidateMediaAction;

        $arguments = $this->getTestArguments(
            $action,
            ['maxHeight' => 319]
        );
        $this->expectInvalidArgumentException(1004);
        $action->run($arguments);
    }

    public function testMinLength(): void
    {
        $action = new ValidateMediaAction;

        $arguments = $this->getTestArguments(
            $action,
            ['minLength' => 6]
        );
        $this->expectInvalidArgumentException(1005);
        $action->run($arguments);
    }

    public function testMaxLength(): void
    {
        $action = new ValidateMediaAction;

        $arguments = $this->getTestArguments(
            $action,
            ['maxLength' => 2]
        );
        $this->expectInvalidArgumentException(1006);
        $action->run($arguments);
    }
}
