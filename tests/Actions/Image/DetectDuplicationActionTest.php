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
use Chevereto\Actions\Image\DetectDuplicationAction;
use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;
use PHPUnit\Framework\TestCase;
use function Chevereto\Image\imageHash;

final class DetectDuplicationActionTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->expectNotToPerformAssertions();
        $action = new DetectDuplicationAction;
        new Arguments(
            $action->parameters(),
            [
                'perceptual' => '63686576657265746f',
                'md5' => 'ad9ad3a94cff902a07058f5be9b2aea0'
            ]
        );
    }
}
