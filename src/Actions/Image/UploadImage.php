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

namespace Chevereto\Actions\Image;

use Chevere\Components\Response\ResponseSuccess;
use Chevere\Interfaces\Response\ResponseInterface;
use Chevere\Interfaces\Workflow\ActionInterface;

class UploadImage implements ActionInterface
{
    // private string $filename;

    public function __construct(string $filename)
    {
        // $this->filename = $filename;
    }

    public function execute(): ResponseInterface
    {
        return new ResponseSuccess(['id' => '123']);
    }
}
