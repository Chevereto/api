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
use Chevere\Components\Serialize\Deserialize;
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Interfaces\Parameter\StringParameterInterface;
use Exception;
use function Safe\copy;

trait FileStoreBinarySourceTrait
{
    /**
     * @param string $source A serialized PHP `$_FILES['source']` variable
     *
     * @throws InvalidArgumentException
     */
    public function assertStoreSource(string $source, string $uploadFile): void
    {
        $deserialize = new Deserialize($source);
        $filename = $deserialize->var()['tmp_name'] ?? null;
        if ($filename === null) {
            throw new Exception();
        }

        copy($filename, $uploadFile);
    }

    private function getBinaryStringParameter(): StringParameterInterface
    {
        return (new StringParameter())
            ->withAddedAttribute('tryFiles');
    }
}
