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

use Chevere\Components\Filesystem\Basename;
use Chevere\Components\Filesystem\Path;
use function Chevere\Components\Str\randomString;
use Chevereto\Actions\File\FileTargetBasenameAction;
use Chevereto\Components\Storage\Storage;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PHPUnit\Framework\TestCase;

final class FileTargetBasenameActionTest extends TestCase
{
    public function testId(): void
    {
        $action = new FileTargetBasenameAction();
        $arguments = $this->getArguments([
            'naming' => 'id',
            'name' => 'test.md',
        ]);
        $basename = new Basename($arguments['name']);
        $response = $action->run($action->getArguments(...$arguments));
        $this->assertSame(
            $arguments['id'] . '.' . $basename->extension(),
            $response->data()['basename']->toString()
        );
    }

    public function testOriginal(): void
    {
        $action = new FileTargetBasenameAction();
        $arguments = $this->getArguments([
            'naming' => 'original',
            'name' => 'test.md',
        ]);
        $basename = new Basename($arguments['name']);
        $response = $action->run($action->getArguments(...$arguments));
        /** @var Basename $responseBasename */
        $responseBasename = $response->data()['basename'];
        $this->assertSame(
            $basename->toString(),
            $responseBasename->toString()
        );
    }

    public function testOriginalFailoverMixed(): void
    {
        $action = new FileTargetBasenameAction();
        $arguments = $this->getArguments([
            'naming' => 'original',
            'name' => 'test.md',
            'path' => new Path('/some/path/'),
        ]);
        $basename = new Basename($arguments['name']);
        $response = $action->run($action->getArguments(...$arguments));
        /** @var Basename $responseBasename */
        $responseBasename = $response->data()['basename'];
        $this->assertNotSame($basename->toString(), $responseBasename->toString());
        $this->assertStringStartsWith(
            $basename->name(),
            $responseBasename->toString()
        );
        $this->assertStringEndsWith(
            '.' . $basename->extension(),
            $responseBasename->toString()
        );
    }

    public function testRandomName(): void
    {
        $action = new FileTargetBasenameAction();
        $arguments = $this->getArguments([
            'naming' => 'random',
            'name' => 'test.md',
        ]);
        $basename = new Basename($arguments['name']);
        $response = $action->run($action->getArguments(...$arguments));
        /** @var Basename $responseBasename */
        $responseBasename = $response->data()['basename'];
        $this->assertSame($basename->extension(), $responseBasename->extension());
        $this->assertNotSame($arguments['name'], $responseBasename->toString());
    }

    public function testMixedName(): void
    {
        $action = new FileTargetBasenameAction();
        $arguments = $this->getArguments([
            'naming' => 'mixed',
            'name' => 'test.md',
        ]);
        $basename = new Basename($arguments['name']);
        $response = $action->run($action->getArguments(...$arguments));
        /** @var Basename $responseBasename */
        $responseBasename = $response->data()['basename'];
        $this->assertSame($basename->extension(), $responseBasename->extension());
        $this->assertNotSame($basename->toString(), $responseBasename->toString());
        $this->assertStringStartsWith($basename->name(), $responseBasename->toString());
        $expectedLength = mb_strlen($basename->toString()) + 16;
        $this->assertTrue(mb_strlen($responseBasename->toString()) === $expectedLength);
    }

    public function testMixedNameTooLong(): void
    {
        $action = new FileTargetBasenameAction();
        $arguments = $this->getArguments([
            'naming' => 'mixed',
            'name' => 'test.md',
        ]);
        $arguments['name'] = randomString(255 - 3) . '.md';
        $response = $action->run($action->getArguments(...$arguments));
        $responseBasename = $response->data()['basename'];
        $this->assertTrue(strlen($responseBasename->toString()) === 255);
    }

    private function getArguments(array $array): array {
        return array_merge([
            'id' => '123',
            'storage' => $this->getStorage(),
            'path' => new Path('/'),
        ], $array);
    }

    private function getStorage(): Storage {
        return new Storage(new LocalFilesystemAdapter(__DIR__ . '/_resources'));
    }
}