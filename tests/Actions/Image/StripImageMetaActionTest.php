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
use Chevereto\Actions\Image\StripImageMetaAction;
use PHPUnit\Framework\TestCase;
use function Chevereto\Image\imageManager;
use function Safe\copy;
use function Safe\unlink;

final class StripImageMetaActionTest extends TestCase
{
    public function testConstruct(): void
    {
        $source = __DIR__ . '/assets/all.jpg';
        $strip = str_replace('.', '-strip.', $source);
        copy($source, $strip);
        $sourceImage = imageManager()->make($source);
        $stripImage = imageManager()->make($strip);
        $action = new StripImageMetaAction;
        $arguments = new Arguments(
            $action->getParameters(),
            ['image' => $stripImage]
        );
        $action->run($arguments);
        $tag = 'GPSAltitude';
        $this->assertIsArray(exif_read_data($source, 'ANY_TAG'));
        $this->assertFalse(exif_read_data($strip, 'ANY_TAG'));
        $this->assertSame('0/10', $sourceImage->exif()[$tag]);
        $this->assertNotContains($tag, $stripImage->exif());
        unlink($strip);
    }
}
