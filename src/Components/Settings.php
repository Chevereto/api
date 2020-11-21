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
use Chevere\Interfaces\To\ToArrayInterface;
use Ds\Map;

final class Settings implements ToArrayInterface
{
    use DescriptorTrait;
    use MapTrait;

    public function __construct(array $settingValue)
    {
        $this->map = new Map;
        $this->map->putAll($settingValue);
    }

    public function withPut(string $setting, string $value): self
    {
        $new = clone $this;
        $new->map->put($setting, $value);

        return $new;
    }

    /**
     * @throws OutOfBoundsException
     */
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
                (new Message('Missing key(s) %keys%'))
                    ->code('%keys%', implode(', ', $missing))
            );
        }
    }

    /**
     * @throws OutOfBoundsException
     */
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

    public function toArray(): array
    {
        return $this->map->toArray();
    }
}
