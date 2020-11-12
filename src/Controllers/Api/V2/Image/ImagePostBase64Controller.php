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

use Chevere\Components\Parameter\StringParameter;
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Exceptions\Core\RuntimeException;
use Chevere\Interfaces\Parameter\StringParameterInterface;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\StreamException;
use function Chevereto\Encoding\assertBase64;
use function Chevereto\Encoding\getBase64Regex;
use function Chevereto\Encoding\storeDecodedBase64;

final class ImagePostBase64Controller extends ImagePostController
{
    public function getDescription(): string
    {
        return 'Uploads a base64 encoded image resource.';
    }

    public function getSourceParameter(): StringParameterInterface
    {
        return (new StringParameter('source'))
            ->withRegex(getBase64Regex())
            ->withDescription('A base64 encoded image string.');
    }

    /**
     * @param string $source A base64 encoded file
     *
     * @throws InvalidArgumentException
     * @throws FilesystemException
     * @throws StreamException
     * @throws RuntimeException
     */
    public function assertStoreSource(string $source, string $path): void
    {
        assertBase64($source);
        storeDecodedBase64($source, $path);
    }
}
