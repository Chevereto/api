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

namespace Chevereto\Controllers\Api\V2\File\Traits;

use Chevere\Components\Workflow\Task;
use Chevere\Interfaces\Workflow\TaskInterface;
use Chevereto\Actions\Storage\FailoverAction;

trait FileStorageFailoverTaskTrait
{
    public function getStorageFailoverTask(): TaskInterface
    {
        return (new Task(FailoverAction::class))
            ->withArguments([
                'storageId' => '${storageId}',
                // 'required' => '${validate:bytes}'
            ]);
    }
}
