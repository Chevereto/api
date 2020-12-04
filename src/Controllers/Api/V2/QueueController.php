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

namespace Chevereto\Controllers\Api\V2;

use Chevere\Components\Action\Controller;
use Chevere\Components\Workflow\Step;
use Chevere\Components\Workflow\Workflow;
use Chevere\Interfaces\Workflow\TaskInterface;
use Chevere\Interfaces\Workflow\WorkflowInterface;

abstract class QueueController extends Controller
{
    /**
     * @return Array<string, TaskInterface>
     */
    abstract public function getSteps(): array;

    final public function getWorkflow(): WorkflowInterface
    {
        $class = get_class($this);
        $name = str_replace('\\', '-', $class);
        $workflow = new Workflow($name);
        foreach ($this->getSteps() as $k => $v) {
            $workflow = $workflow->withAdded(new Step($k), $v);
        }

        return $workflow;
    }
}
