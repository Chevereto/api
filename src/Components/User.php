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

namespace Chevereto\Components;

use Chevere\Components\Description\Traits\DescriptionTrait;
use Chevere\Interfaces\Service\ServiceInterface;

final class User implements ServiceInterface
{
    use DescriptionTrait;

    private int $id = 0;

    public function id(): int
    {
        return $this->id;
    }
}
