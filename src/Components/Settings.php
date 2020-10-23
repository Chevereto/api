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

use Chevere\Components\DataStructures\Traits\MapTrait;
use Chevere\Components\Description\Traits\DescriptorTrait;
use Chevere\Components\Message\Message;
use Chevere\Exceptions\Core\OutOfBoundsException;
use Chevere\Interfaces\Service\ServiceInterface;

final class Settings implements ServiceInterface
{
    use DescriptorTrait;
    use MapTrait;

    public function withPut(string $setting, string $value): self
    {
        $new = clone $this;
        $new->map->put($setting, $value);

        return $new;
    }

    public function assertHasKey(string ...$key): void
    {
        $missing = [];
        foreach ($key as $k) {
            if (!$this->map->hasKey($k)) {
                $missing[] = $k;
            }
        }
        if ($missing !== []) {
            throw new OutOfBoundsException(
                (new Message('Missing key(s): %keys%'))
                    ->code('%keys%', implode(', ', $missing))
            );
        }
    }

    public function get(string $key): string
    {
        try {
            /** @var string $return */
            $return = $this->map->get($key);
        } catch (\OutOfBoundsException $e) {
            throw new OutOfBoundsException(
                (new Message('Key %key% not found'))->code('%key%', $key)
            );
        }

        return $return;
    }
}
