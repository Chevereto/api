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

namespace Chevereto\Controllers\Api\V2\Video;

use Chevere\Components\Workflow\Workflow;
use Chevere\Components\Workflow\WorkflowRun;
use function Chevere\Components\Workflow\workflowRunner;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Chevere\Interfaces\Workflow\WorkflowInterface;
use Chevereto\Controllers\Api\V2\File\FilePostController;

abstract class VideoPostController extends FilePostController
{
    public function getWorkflow(): WorkflowInterface
    {
        return new Workflow(self::class);
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $source = $arguments->getString('source');

        $uploadFile = tempnam(sys_get_temp_dir(), 'chv.temp');
        $this->assertStoreSource($source, $uploadFile);
        $settings = $this->settings
            ->withPut('filename', $uploadFile)
            ->withPut('albumId', '');
        $settings = $settings->toArray();
        unset($settings['apiV1Key']);
        $workflowRun = workflowRunner(
            new WorkflowRun(
                $this->workflow,
                $settings
            )
        );
        $data = $workflowRun->get('upload')->data();
        $raw = json_encode($data, JSON_PRETTY_PRINT);

        return $this->getResponse(raw: $raw);
    }
}
