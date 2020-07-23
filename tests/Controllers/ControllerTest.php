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

namespace Chevereto\Tests\Controllers;

use Chevereto\Controllers\Api\Album\AlbumGetController;
use PHPUnit\Framework\TestCase;

final class ControllerTest extends TestCase
{
    public function testSome(): void
    {
        $this->expectNotToPerformAssertions();
        $controller = new AlbumGetController;
        $controller->getDescription();
        $controller->getParameters();
    }
}
