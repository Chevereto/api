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

use Chevere\Interfaces\Response\ResponseInterface;
use Chevereto\Actions\Image\ImageFixOrientationAction;
use function Chevereto\Image\imageManager;
use PHPUnit\Framework\TestCase;

final class ImageFixOrientationActionTest extends TestCase
{
    public function testConstruct(): void
    {
        $source = __DIR__ . '/assets/right-mirrored.jpg';
        $orient = str_replace('.', '-orient.', $source);
        copy($source, $orient);
        $sourceImage = imageManager()->make($source);
        $orientImage = imageManager()->make($orient);
        $action = new ImageFixOrientationAction();
        $this->assertSame(7, $sourceImage->exif()['Orientation']);
        $response = $action->run($action->getArguments(image: $orientImage));
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(0, $orientImage->exif()['Orientation']);
        if (! unlink($orient)) {
            $this->markTestIncomplete("Failed to remove ${orient}");
        }
    }
}
