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

namespace Chevereto\Tests\Actions\Video;

use Chevere\Components\Parameter\Arguments;
use Chevereto\Actions\Video\ValidateMediaAction;
use PHPUnit\Framework\TestCase;

final class ValidateMediaActionTest extends TestCase
{
    public function testConstruct(): void
    {
        $action = new ValidateMediaAction;
        $arguments = new Arguments($action->parameters(), [
            'filename' => __DIR__ . '/assets/small.webm',
            'maxHeight' => 20000,
            'maxWidth' => 20000,
            'maxLength' => 3600,
            'minHeight' => 0,
            'minWidth' => 0,
            'minLength' => 0,
        ]);
        $this->expectNotToPerformAssertions();
        $action->run($arguments);
    }
}
