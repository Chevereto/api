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

namespace Chevereto\Components\Filesystem;

use Chevere\Components\Message\Message;
use Chevere\Exceptions\Core\InvalidArgumentException;

final class Basename
{
    const MAX_LENGTH_BYTES = 255;

    private string $basename;

    private string $extension;

    private string $name;    

    public function __construct(string $basename)
    {
        if(mb_strlen($basename) > self::MAX_LENGTH_BYTES) {
            throw new InvalidArgumentException(
                message: (new Message('String %string% provided exceed the limit of %bytes% bytes'))
                    ->code('%string%', $basename)
                    ->code('%string%', (string) self::MAX_LENGTH_BYTES),
            );
        }
        $this->basename = $basename;
        $this->extension = strtolower(pathinfo($this->basename, PATHINFO_EXTENSION));
        $this->name = $this->basename;
        if($this->extension !== '') {
            $this->name = substr($this->basename, 0, - (strlen($this->extension) + 1));
        }
    }

    public function toString(): string
    {
        return $this->basename;
    }

    public function extension(): string
    {
        return $this->extension;
    }

    public function name(): string
    {
        return $this->name;
    }
}