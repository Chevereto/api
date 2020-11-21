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
use Chevereto\Actions\File\DetectDuplicateAction;
use PHPUnit\Framework\TestCase;

final class DetectDuplicateActionTest extends TestCase
{
    public function testConstruct(): void
    {
        $this->expectNotToPerformAssertions();
        $action = new DetectDuplicateAction;
        new Arguments(
            $action->parameters(),
            [
                'md5' => 'ad9ad3a94cff902a07058f5be9b2aea0',
                'perceptual' => '63686576657265746f',
                'ip' => '1.1.1.1',
                'ipVersion' => '4',
            ]
        );
    }
}
