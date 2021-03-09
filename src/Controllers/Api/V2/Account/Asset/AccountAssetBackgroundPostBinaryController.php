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

namespace Chevereto\Controllers\Api\V2\Account\Asset;

final class AccountAssetBackgroundPostBinaryController extends AccountAssetPostBinaryController
{
    public function getDescription(): string
    {
        return 'Uploads a binary image resource to be used as account background';
    }
}
