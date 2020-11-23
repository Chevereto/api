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

namespace Chevereto\Controllers\Api\V2\Image\Traits;

use Chevere\Components\Workflow\Task;
use Chevere\Interfaces\Workflow\TaskInterface;
use Chevereto\Actions\File\DetectDuplicateAction;

trait ImageDetectDuplicateTaskTrait
{
    public function getDetectDuplicateTask(): TaskInterface
    {
        return (new Task(DetectDuplicateAction::class))
            ->withArguments([
                'md5' => '${validate:md5}',
                'perceptual' => '${validate:perceptual}',
                'ip' => '${ip}',
                'ipVersion' => '${ipVersion}',
            ]);
    }
}
