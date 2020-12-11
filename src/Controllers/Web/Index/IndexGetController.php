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

namespace Chevereto\Controllers\Web\Index;

use Chevere\Components\Action\Controller;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Response\ResponseSuccessInterface;

class IndexGetController extends Controller
{
    public function getDescription(): string
    {
        return 'Presents the user interface.';
    }

    public function run(ArgumentsInterface $arguments): ResponseSuccessInterface
    {
        return $this->getResponseSuccess([]);
    }
}
