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

namespace Chevereto\Tests\Controllers\Api\V2\Image;

use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Interfaces\Workflow\WorkflowInterface;
use Chevereto\Controllers\Api\V2\Image\ImagePostUrlController;
use PHPUnit\Framework\TestCase;
use function Safe\file_get_contents;

final class ImagePostUrlControllerTest extends TestCase
{
    public function testParameters(): void
    {
        $controller = new ImagePostUrlController;
        $sourceString = 'http://test.com';
        $source = $controller->getSourceParameter();
        $this->assertSame($sourceString, $source->regex()->match($sourceString)[0]);
    }

    public function testWorkflow(): void
    {
        $this->assertInstanceOf(
            WorkflowInterface::class,
            (new ImagePostUrlController)->getWorkflow()
        );
    }

    public function testAssertStoreSource(): void
    {
        $source = 'https://1.1.1.1/';
        $path = __DIR__ . '/tmp';
        $controller = new ImagePostUrlController;
        $controller->assertStoreSource($source, $path);
        $this->assertStringEqualsFile($path, file_get_contents($source));
        unlink($path);
        $this->expectException(InvalidArgumentException::class);
        $controller->assertStoreSource('httpn://error', $path);
    }
}
