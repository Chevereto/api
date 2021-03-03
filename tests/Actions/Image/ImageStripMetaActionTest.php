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

use Chevereto\Actions\Image\ImageStripMetaAction;
use function Chevereto\Components\Image\imageManager;
use PHPUnit\Framework\TestCase;
use function Safe\copy;
use function Safe\unlink;

final class ImageStripMetaActionTest extends TestCase
{
    public function testConstruct(): void
    {
        $source = __DIR__ . '/assets/all.jpg';
        $strip = str_replace('.', '-strip.', $source);
        copy($source, $strip);
        $sourceImage = imageManager()->make($source);
        $stripImage = imageManager()->make($strip);
        $action = new ImageStripMetaAction();
        $arguments = [
            'image' => $stripImage,
        ];
        $action->run($action->getArguments(...$arguments));
        $tag = 'GPSAltitude';
        $this->assertIsArray(exif_read_data($source, 'ANY_TAG'));
        $this->assertFalse(exif_read_data($strip, 'ANY_TAG'));
        $this->assertSame('0/10', $sourceImage->exif()[$tag]);
        $this->assertNotContains($tag, $stripImage->exif());
        unlink($strip);
    }
}
