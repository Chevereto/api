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

use Chevere\Components\Message\Message;
use Chevere\Components\Parameter\StringParameter;
use Chevere\Components\Serialize\Unserialize;
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Interfaces\Parameter\StringParameterInterface;
use Safe\Exceptions\FilesystemException;
use Throwable;
use function Safe\copy;

final class ImagePostBinaryController extends ImagePostController
{
    public function getDescription(): string
    {
        return 'Uploads a binary image resource.';
    }

    public function getSourceParameter(): StringParameterInterface
    {
        return (new StringParameter('source'))
            ->withAddedAttribute('tryFiles')
            ->withDescription('A binary image.');
    }

    /**
     * @param string $source A serialized PHP `$_FILES['source']` variable
     *
     * @throws InvalidArgumentException
     * @throws FilesystemException
     */
    public function assertStoreSource(string $source, string $path): void
    {
        try {
            $unserialize = new Unserialize($source);
            $filename = $unserialize->var()['tmp_name'];
        } catch (Throwable $e) {
            throw new InvalidArgumentException(
                new Message('Invalid binary file'),
            );
        }
        copy($filename, $path);
    }
}
