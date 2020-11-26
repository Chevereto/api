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

use Chevereto\Actions\Image\FetchMetaAction;
use PHPUnit\Framework\TestCase;
use Tests\Actions\Traits\ExpectInvalidArgumentExceptionCodeTrait;
use function Chevereto\Image\imageManager;

final class FetchMetaActionTest extends TestCase
{
    use ExpectInvalidArgumentExceptionCodeTrait;

    public function testExif(): void
    {
        $action = new FetchMetaAction;
        $arguments = ['image' => imageManager()->make(__DIR__ . '/assets/exif.jpg')];
        $response = $action->run($arguments);
        $this->assertIsArray($response->data()['exif']);
        $this->assertCount(0, $response->data()['iptc']);
        $this->assertCount(0, $response->data()['xmp']);
    }

    public function testIptc(): void
    {
        $action = new FetchMetaAction;
        $arguments = ['image' => imageManager()->make(__DIR__ . '/assets/iptc.jpg')];
        $response = $action->run($arguments);
        $this->assertIsArray($response->data()['exif']);
        $this->assertIsArray($response->data()['iptc']);
        $this->assertCount(0, $response->data()['xmp']);
    }

    public function testXmp(): void
    {
        $action = new FetchMetaAction;
        $arguments = ['image' => imageManager()->make(__DIR__ . '/assets/all.jpg')];
        $response = $action->run($arguments);
        $this->assertIsArray($response->data()['exif']);
        $this->assertIsArray($response->data()['iptc']);
        $this->assertIsArray($response->data()['xmp']);
    }
}
