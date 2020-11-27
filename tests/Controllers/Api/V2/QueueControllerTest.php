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

namespace Chevereto\Tests\Controllers\Api\V2;

use Chevere\Components\Workflow\Task;
use Chevere\Exceptions\Core\OutOfBoundsException;
use Chevere\Exceptions\Service\ServiceException;
use Chevere\Interfaces\Response\ResponseSuccessInterface;
use Chevereto\Actions\File\FileValidateAction;
use Chevereto\Components\Settings;
use Chevereto\Controllers\Api\V2\QueueController;
use PHPUnit\Framework\TestCase;

final class QueueControllerTest extends TestCase
{
    public function testWithoutSettings(): void
    {
        $controller = new TestQueueControllerTest;
        $this->expectException(ServiceException::class);
        $controller->settings();
    }

    public function testWithSettings(): void
    {
        $controller = new TestQueueControllerTest;
        $settings = new Settings(['test' => '123']);
        $controller = $controller->withSettings($settings);
        $this->assertSame($settings, $controller->settings());
        $this->expectException(OutOfBoundsException::class);
        $controller->withSettings(new Settings([]));
    }
}

final class TestQueueControllerTest extends QueueController
{
    public function getSettingsKeys(): array
    {
        return ['test'];
    }

    public function getSteps(): array
    {
        return ['step' => new Task(FileValidateAction::class)];
    }

    public function run(array $arguments): ResponseSuccessInterface
    {
        return $this->getResponseSuccess([]);
    }
}
