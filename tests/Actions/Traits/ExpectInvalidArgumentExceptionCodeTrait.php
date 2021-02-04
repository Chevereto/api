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

namespace Tests\Actions\Traits;

use Chevere\Exceptions\Core\InvalidArgumentException;

trait ExpectInvalidArgumentExceptionCodeTrait
{
    abstract public function expectException(string $exception): void;

    abstract public function expectExceptionCode($code): void;

    private function expectInvalidArgumentException(int $code): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode($code);
    }
}
