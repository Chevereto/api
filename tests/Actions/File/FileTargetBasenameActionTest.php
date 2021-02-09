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

use Chevere\Components\Filesystem\Path;
use Chevereto\Actions\File\FileTargetBasenameAction;
use Chevereto\Components\Storage\Storage;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PHPUnit\Framework\TestCase;

final class FileTargetBasenameActionTest extends TestCase
{
    public function testId(): void
    {
        $action = new FileTargetBasenameAction();
        $arguments = [
            'id' => '123',
            'name' => 'test.md',
            'naming' => 'id',
            'storage' => $this->getStorage(),
            'path' => new Path('/some/path/'),
        ];
        $response = $action->run($action->getArguments(...$arguments));
        $this->assertSame('123.md', $response->data()['basename']->toString());
    }

    private function getStorage(): Storage {
        return new Storage(new LocalFilesystemAdapter(__DIR__ . '/_resources'));
    }
}