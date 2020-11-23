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

use Chevere\Components\Parameter\StringParameter;
use Chevere\Interfaces\Parameter\StringParameterInterface;
use function Chevereto\Encoding\assertBase64;
use function Chevereto\Encoding\getBase64Regex;
use function Chevereto\Encoding\storeDecodedBase64;

trait FileStoreBase64SourceTrait
{
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

    private function getBase64StringParameter(string $name): StringParameterInterface
    {
        return (new StringParameter($name))
            ->withRegex(getBase64Regex());
    }
}
