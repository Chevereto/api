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

namespace Chevereto\Controllers\Api\V2\Video;

use Chevere\Interfaces\Parameter\StringParameterInterface;
use Chevereto\Controllers\Api\V2\File\Traits\AssertStoreBase64SourceTrait;

// final class VideoPostBase64Controller extends VideoPostController
// {
//     use AssertStoreBase64SourceTrait;

//     public function getDescription(): string
//     {
//         return 'Uploads a base64 encoded video resource.';
//     }

//     public function getSourceParameter(): StringParameterInterface
//     {
//         return $this->getBase64SourceParameter()
//             ->withDescription('A base64 encoded video string.');
//     }
// }
