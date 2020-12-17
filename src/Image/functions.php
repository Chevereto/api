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

namespace Chevereto\Image;

use Chevere\Exceptions\Core\LogicException;
use Intervention\Image\ImageManager;
use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;

function imageManager(): ImageManager
{
    try {
        return ImageManagerInstance::get();
    } catch (LogicException $e) {
        new ImageManagerInstance(
            new ImageManager([
                'driver' => 'Imagick',
            ])
        );

        return ImageManagerInstance::get();
    }
}

function imageHash(): ImageHash
{
    try {
        return ImageHashInstance::get();
    } catch (LogicException $e) {
        new ImageHashInstance(
            new ImageHash(new DifferenceHash(16))
        );

        return ImageHashInstance::get();
    }
}
