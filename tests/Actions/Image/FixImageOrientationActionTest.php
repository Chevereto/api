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
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevereto\Actions\Image\FixImageOrientationAction;
use PHPUnit\Framework\TestCase;

final class FixImageOrientationActionTest extends TestCase
{
    public function testConstruct(): void
    {
        $action = new FixImageOrientationAction;
        $this->expectException(InvalidArgumentException::class);
        $arguments = new Arguments(
            $action->parameters(),
            [
                'image' => __DIR__ . '/assets/Landscape_3-alt.jpg',
            ]
        );
        $action->run($arguments);
    }
}
