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

namespace Chevereto\ImageManager;

use Intervention\Image\ImageManager;
use LogicException;

function imageManager(): ImageManager
{
    try {
        return ImageManagerInstance::get();
    } catch (LogicException $e) {
        new ImageManagerInstance(
            new ImageManager(['driver' => 'Imagick'])
        );

        return ImageManagerInstance::get();
    }
}
