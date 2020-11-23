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

namespace Chevereto\Controllers\Api\V2\Image;

use Chevere\Interfaces\Parameter\StringParameterInterface;
use Chevereto\Controllers\Api\V2\File\Traits\FileStoreBase64SourceTrait;

final class ImagePostBase64Controller extends ImagePostController
{
    use FileStoreBase64SourceTrait;

    public function getDescription(): string
    {
        return 'Uploads a base64 encoded image resource.';
    }

    public function getSourceParameter(): StringParameterInterface
    {
        return $this->getBase64StringParameter('source')
            ->withDescription('A base64 encoded image string.');
    }
}
