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

use Chevere\Components\DataStructure\Map;
use Chevere\Components\Filesystem\Path;
use Chevere\Components\Parameter\Arguments;
use Chevere\Components\Workflow\WorkflowRun;
use Chevere\Components\Workflow\WorkflowRunner;
use Chevere\Interfaces\Workflow\WorkflowInterface;
use Chevere\Interfaces\Workflow\WorkflowResponseInterface;
use Chevereto\Components\Database\Database;
use Chevereto\Controllers\Api\V1\Upload\UploadPostController;
use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\TestCase;

final class UploadPostControllerTest extends TestCase
{
    public function testWithContext(): void
    {
        $connectionParams = [
            'dbname' => 'chevereto-4',
            'user' => 'root',
            'password' => 'root',
            'host' => 'localhost',
            'driver' => 'pdo_mysql',
        ];
        $connection = DriverManager::getConnection($connectionParams);
        $database = new Database($connection);
        $source = [
            'tmp_name' => __DIR__ . '/assets/favicon.png',
        ];
        $request = [
            'source' => serialize($source),
            'key' => 'test',
            'format' => 'json',
        ];
        $context = [
            'albumId' => 123,
            'ip' => '127.0.0.1',
            'ipVersion' => '4',
            'maxBytes' => 20000000,
            'maxHeight' => 20000,
            'maxWidth' => 20000,
            'expires' => 0,
            'mimes' => 'image/png',
            'minBytes' => 0,
            'minHeight' => 20,
            'minWidth' => 20,
            'name' => 'DSC-TEST.jpg',
            'naming' => 'original',
            'path' => new Path('/2021/03/06/'),
            'table' => 'images',
            'userId' => 123,
        ];
        $controller = new UploadPostController();
        $parameters = $controller->parameters();
        $runArguments = new Arguments($parameters, ...$request);
        $response = $controller->run($runArguments);
        $this->assertInstanceOf(WorkflowResponseInterface::class, $response);
        $workflow = $controller->getWorkflow();
        $options = array_merge($response->data(), $context);
        $runner = new WorkflowRunner(
            new WorkflowRun($workflow, ...$options)
        );
        $dependencies = new Map(database: $database);
        $runner->run($dependencies);
        $workflow = $controller->getWorkflow();
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
