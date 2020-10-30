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

use Chevere\Components\Message\Message;
use Chevere\Exceptions\Core\LogicException;
use Jenssegers\ImageHash\ImageHash;

/**
 * @codeCoverageIgnore
 */
final class ImageHashInstance
{
    private static ImageHash $instance;

    public function __construct(ImageHash $imageHash)
    {
        self::$instance = $imageHash;
    }

    public static function get(): ImageHash
    {
        if (!isset(self::$instance)) {
            throw new LogicException(
                (new Message('No %instance% instance present'))
                    ->code('%instance%', ImageHash::class)
            );
        }

        return self::$instance;
    }
}
