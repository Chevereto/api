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

use Chevere\Components\Filesystem\Filename;
use Chevere\Components\Filesystem\Path;
use function Chevere\Components\Str\randomString;
use Chevereto\Actions\File\FileNamingAction;
use Chevereto\Components\Storage\Storage;
use League\Flysystem\Local\LocalFilesystemAdapter;
use PHPUnit\Framework\TestCase;

final class FileNamingActionTest extends TestCase
{
    public function testId(): void
    {
        $action = new FileNamingAction();
        $options = $this->getArguments([
            'naming' => 'id',
            'name' => 'test.md',
        ]);
        $filename = new Filename($options['name']);
        $arguments = $action->getArguments(...$options);
        $response = $action->run($arguments);
        $this->assertSame(
            'encoded' . '.' . $filename->extension(),
            $response->data()['filename']->toString()
        );
    }

    public function testOriginal(): void
    {
        $action = new FileNamingAction();
        $arguments = $this->getArguments([
            'naming' => 'original',
            'name' => 'test.md',
        ]);
        $filename = new Filename($arguments['name']);
        $response = $action->run($action->getArguments(...$arguments));
        /** @var Filename $responseFilename */
        $responseFilename = $response->data()['filename'];
        $this->assertSame(
            $filename->toString(),
            $responseFilename->toString()
        );
    }

    public function testOriginalFailoverMixed(): void
    {
        $action = new FileNamingAction();
        $arguments = $this->getArguments([
            'naming' => 'original',
            'name' => 'test.md',
            'path' => new Path('/some/path/'),
        ]);
        $filename = new Filename($arguments['name']);
        $response = $action->run($action->getArguments(...$arguments));
        /** @var Filename $responseFilename */
        $responseFilename = $response->data()['filename'];
        $this->assertNotSame($filename->toString(), $responseFilename->toString());
        $this->assertStringStartsWith(
            $filename->name(),
            $responseFilename->toString()
        );
        $this->assertStringEndsWith(
            '.' . $filename->extension(),
            $responseFilename->toString()
        );
    }

    public function testRandomName(): void
    {
        $action = new FileNamingAction();
        $arguments = $this->getArguments([
            'naming' => 'random',
            'name' => 'test.md',
        ]);
        $filename = new Filename($arguments['name']);
        $response = $action->run($action->getArguments(...$arguments));
        /** @var Filename $responseFilename */
        $responseFilename = $response->data()['filename'];
        $this->assertSame($filename->extension(), $responseFilename->extension());
        $this->assertNotSame($arguments['name'], $responseFilename->toString());
    }

    public function testMixedName(): void
    {
        $action = new FileNamingAction();
        $arguments = $this->getArguments([
            'naming' => 'mixed',
            'name' => 'test.md',
        ]);
        $filename = new Filename($arguments['name']);
        $response = $action->run($action->getArguments(...$arguments));
        /** @var Filename $responseFilename */
        $responseFilename = $response->data()['filename'];
        $this->assertSame($filename->extension(), $responseFilename->extension());
        $this->assertNotSame($filename->toString(), $responseFilename->toString());
        $this->assertStringStartsWith($filename->name(), $responseFilename->toString());
        $expectedLength = mb_strlen($filename->toString()) + 16;
        $this->assertTrue(mb_strlen($responseFilename->toString()) === $expectedLength);
    }

    public function testMixedNameTooLong(): void
    {
        $action = new FileNamingAction();
        $arguments = $this->getArguments([
            'naming' => 'mixed',
            'name' => 'test.md',
        ]);
        $arguments['name'] = randomString(255 - 3) . '.md';
        $response = $action->run($action->getArguments(...$arguments));
        /** @var Filename $responseFilename */
        $responseFilename = $response->data()['filename'];
        $this->assertTrue(strlen($responseFilename->toString()) === 255);
    }

    private function getArguments(array $array): array
    {
        return array_merge([
            'id' => 123,
            'storage' => $this->getStorage(),
            'path' => new Path('/'),
        ], $array);
    }

    private function getStorage(): Storage
    {
        return new Storage(new LocalFilesystemAdapter(__DIR__ . '/_resources'));
    }
}
