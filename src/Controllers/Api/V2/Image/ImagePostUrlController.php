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
use Chevere\Components\Regex\Regex;
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Interfaces\Parameter\StringParameterInterface;
use GuzzleHttp\Client;
use Safe\Exceptions\FilesystemException;
use Throwable;
use function Safe\file_put_contents;

final class ImagePostUrlController extends ImagePostController
{
    public function getDescription(): string
    {
        return 'Uploads an image URL image resource.';
    }

    public function getSourceParameter(): StringParameterInterface
    {
        return (new StringParameter('source'))
            ->withRegex(new Regex('/^(https?|ftp)+\:\/\/.+$/'))
            ->withDescription('An image URL.');
    }

    /**
     * @param string $source An URI with response body
     *
     * @throws InvalidArgumentException
     * @throws FilesystemException
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
}
