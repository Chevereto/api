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

namespace Chevereto\Tests\Actions\Image;

use Chevere\Exceptions\Core\LogicException;
use Chevereto\Actions\Image\InsertAction;
use PHPUnit\Framework\TestCase;

final class InsertActionTest extends TestCase
{
    public function testConstruct(): void
    {
        $action = new InsertAction;
        $arguments = [
            'expires' => 0,
            'userId' => 0,
            'albumId' => 0,
        ];
        $this->expectException(LogicException::class);
        $action->run($arguments);
    }
}
