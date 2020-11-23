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

trait ImageSettingsKeysTrait
{
    public function getSettingsKeys(): array
    {
        return [
            'extensions',
            'maxBytes',
            'maxHeight',
            'maxWidth',
            'minBytes',
            'minHeight',
            'minWidth',
            'naming',
            'storageId',
            'uploadPath',
            'userId'
        ];
    }
}
