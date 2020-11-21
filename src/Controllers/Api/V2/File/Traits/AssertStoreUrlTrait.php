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
use Chevere\Components\Regex\Regex;
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Interfaces\Parameter\StringParameterInterface;
use GuzzleHttp\Client;
use Throwable;

trait AssertStoreUrlTrait
{
    /**
     * @throws InvalidArgumentException
     */
    public function assertStoreSource(string $source, string $path): void
    {
        try {
            $client = new Client([
                'base_uri' => $source,
                'timeout' => 2,
            ]);
            $response = $client->request('GET');
        } catch (Throwable $e) {
            throw new InvalidArgumentException(
                (new Message($e->getMessage()))
            );
        }
        file_put_contents($path, $response->getBody());
    }

    private function getUrlStringParameter(string $name): StringParameterInterface
    {
        return (new StringParameter($name))
            ->withRegex(new Regex('/^(https?|ftp)+\:\/\/.+$/'));
    }
}
