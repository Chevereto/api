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

use Chevere\Components\Controller\Controller;
use Chevere\Components\Message\Message;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Parameter\StringParameter;
use Chevere\Components\Str\StrBool;
use Chevere\Exceptions\Core\Exception;
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Service\ServiceableInterface;
use Chevereto\Controllers\Api\V2\Image\Traits\ImagePostTrait;

final class ImagePostBase64Controller extends Controller implements ServiceableInterface
{
    use ImagePostTrait;

    public function withSetUp(): self
    {
        $new = clone $this;
        $new->workflow = $this->getWorkflow();

        return $new;
    }

    public function getDescription(): string
    {
        return 'Uploads a base64 encoded image resource.';
    }

    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAddedRequired(
                (new StringParameter('source'))
                    ->withDescription('A base64 image string.')
            );
    }

    public function assertStoreSource($source, string $uploadFile): void
    {
        try {
            $this->assertBase64String($source);
            $this->storeDecodedBase64String($source, $uploadFile);
        } catch (Exception $e) {
            throw new InvalidArgumentException(
                new Message('Invalid base64 string'),
                $e->getCode()
            );
        }
    }

    public function assertBase64String(string $string): void
    {
        $double = base64_encode(base64_decode($string));
        if (!(new StrBool($string))->same($double)) {
            throw new Exception(
                new Message('Invalid base64 formatting'),
                1100
            );
        }
        unset($double);
    }

    public function storeDecodedBase64String(string $base64, string $path): void
    {
        $fh = fopen($path, 'w');
        stream_filter_append($fh, 'convert.base64-decode', STREAM_FILTER_WRITE);
        if (!fwrite($fh, $base64)) {
            throw new Exception(
                new Message('Unable to store decoded base64 string'),
                1200
            );
        }
        fclose($fh);
    }
}
