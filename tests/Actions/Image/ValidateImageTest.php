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

namespace Chevereto\Tests\Actions\Image;

use Chevere\Components\Parameter\Arguments;
use Chevereto\Actions\Image\ValidateImage;
use PHPUnit\Framework\TestCase;

final class ValidateImageTest extends TestCase
{
    private function getTestArguments(array $arguments): array
    {
        return array_replace([
            'filename' => __DIR__ . '/assets/favicon.png',
            'maxWidth' => '1000',
            'maxHeight' => '1000',
            'minWidth' => '100',
            'minHeight' => '100',
        ], $arguments);
    }

    public function testConstruct(): void
    {
        $action = new ValidateImage;
        $arguments = new Arguments(
            $action->parameters(),
            $this->getTestArguments([])
        );
        $response = $action->run($arguments);
        $this->assertSame(
            [
                'width' => 300,
                'height' => 300,
            ],
            $response->data()
        );
    }

    public function testMaxWidth(): void
    {
        $action = new ValidateImage;
        $arguments = new Arguments(
            $action->parameters(),
            $this->getTestArguments([
                'maxWidth' => '299',
            ])
        );
        $response = $action->run($arguments);
        $this->assertSame(1100, $response->data()['code']);
    }

    public function testMaxHeight(): void
    {
        $action = new ValidateImage;
        $arguments = new Arguments(
            $action->parameters(),
            $this->getTestArguments([
                'maxHeight' => '299',
            ])
        );
        $response = $action->run($arguments);
        $this->assertSame(1101, $response->data()['code']);
    }

    public function testMinWidth(): void
    {
        $action = new ValidateImage;
        $arguments = new Arguments(
            $action->parameters(),
            $this->getTestArguments([
                'minWidth' => '301',
            ])
        );
        $response = $action->run($arguments);
        $this->assertSame(1102, $response->data()['code']);
    }

    public function testMinHeight(): void
    {
        $action = new ValidateImage;
        $arguments = new Arguments(
            $action->parameters(),
            $this->getTestArguments([
                'minHeight' => '301',
            ])
        );
        $response = $action->run($arguments);
        $this->assertSame(1103, $response->data()['code']);
    }
}
