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
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Exceptions\Core\RuntimeException;
use Chevere\Interfaces\Parameter\StringParameterInterface;
use function Chevereto\Components\Encoding\assertBase64;
use function Chevereto\Components\Encoding\getBase64Regex;
use function Chevereto\Components\Encoding\storeDecodedBase64;
use Safe\Exceptions\StreamException;

trait FileStoreBase64SourceTrait
{
    /**
     * @param string $source A base64 encoded file
     *
     * @throws InvalidArgumentException
     * @throws StreamException
     * @throws RuntimeException
     */
    public function assertStoreSource(string $source, string $uploadFile): void
    {
        assertBase64($source);
        storeDecodedBase64($source, $uploadFile);
    }

    private function getBase64StringParameter(): StringParameterInterface
    {
        return (new StringParameter())
            ->withRegex(getBase64Regex());
    }
}
