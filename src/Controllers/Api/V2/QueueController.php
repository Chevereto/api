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
use Chevere\Components\Workflow\Workflow;
use Chevere\Interfaces\Workflow\WorkflowInterface;

abstract class QueueController extends Controller
{
    public function getWorkflow(): WorkflowInterface
    {
        return new Workflow(self::class);
    }
}
