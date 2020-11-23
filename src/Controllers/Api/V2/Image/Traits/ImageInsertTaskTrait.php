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
use Chevereto\Actions\Image\InsertAction;

trait ImageInsertTaskTrait
{
    public function getInsertTask(): TaskInterface
    {
        return (new Task(InsertAction::class))
            ->withArguments([
                'albumId' => '${albumId}',
                // 'exif' => '${fetch-meta:exif}',
                'expires' => '${expires}',
                // 'image' => '${validate:image}',
                // 'iptc' => '${fetch-meta:iptc}',
                // 'md5' => '${validate:md5}',
                // 'perceptual' => '${validate:perceptual}',
                // 'storageId' => '${storage-failover:storageId}',
                'userId' => '${userId}',
                // 'xmp' => '${fetch-meta:xmp}',
            ]);
    }
}
