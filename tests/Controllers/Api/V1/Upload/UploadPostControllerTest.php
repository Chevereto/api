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

namespace Tests\Controllers\Api\V1\Upload;

use Chevere\Interfaces\Workflow\WorkflowInterface;
use Chevereto\Controllers\Api\V1\Upload\UploadPostController;
use PHPUnit\Framework\TestCase;

final class UploadPostControllerTest extends TestCase
{
    public function testWithContext(): void
    {
        $context = [
            'apiV1Key' => 'api-key-value',
            'mimes' => 'text/x-php',
            'maxBytes' => 20000000,
            'maxHeight' => 20000,
            'maxWidth' => 20000,
            'minBytes' => 0,
            'minHeight' => 20,
            'minWidth' => 20,
            'naming' => 'original',
            'path' => '2020/10/23',
            'userId' => 123,
        ];
        $controller = (new UploadPostController())->withContextArguments(...$context);
        $this->assertSame($context, $controller->contextArguments()->toArray());
    }

    public function testWorkflow(): void
    {
        $workflow = (new UploadPostController())->getWorkflow();
        $this->assertInstanceOf(
            WorkflowInterface::class,
            $workflow
        );
    }
}
