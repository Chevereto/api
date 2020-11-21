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

namespace Chevereto\Components;

use Chevere\Interfaces\Response\ResponseInterface;
use Chevere\Interfaces\Workflow\WorkflowMessageInterface;

final class Enqueue
{
    public function __invoke(WorkflowMessageInterface $workflowMessage, ResponseInterface $response)
    {
        // xdd($workflowMessage);
        // xdd(['Redis + RabbitMQ: (serialized)']);
    }
}
