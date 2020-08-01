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

namespace Chevereto\Services\Upload\Image;

use Chevere\Interfaces\Service\ServiceInterface;

final class UploadImagePublicService implements ServiceInterface
{
    public function __construct()
    {
    }

    public function getDescription(): string
    {
        return 'Provides public image uploading service.';
    }
}
