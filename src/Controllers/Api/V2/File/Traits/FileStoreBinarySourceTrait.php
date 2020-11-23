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

namespace Chevereto\Controllers\Api\V2\File\Traits;

use Chevere\Components\Message\Message;
use Chevere\Components\Parameter\StringParameter;
use Chevere\Components\Serialize\Unserialize;
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Interfaces\Parameter\StringParameterInterface;
use Throwable;
use function Chevereto\Encoding\getBase64Regex;
use function Safe\copy;

trait FileStoreBinarySourceTrait
{
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

    private function getBinaryStringParameter(string $name): StringParameterInterface
    {
        return (new StringParameter($name))
            ->withRegex(getBase64Regex())
            ->withAddedAttribute('tryFiles');
    }
}
