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

namespace Chevereto\Encoding;

use Chevere\Components\Message\Message;
use Chevere\Components\Regex\Regex;
use Chevere\Components\Str\StrBool;
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Exceptions\Core\RuntimeException;
use Chevere\Interfaces\Regex\RegexInterface;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\StreamException;
use function Safe\fclose;
use function Safe\fopen;
use function Safe\fwrite;
use function Safe\stream_filter_append;

/**
 * @throws InvalidArgumentException
 */
function assertBase64(string $string): void
{
    $double = base64_encode(base64_decode($string, true));
    if (! (new StrBool($string))->same($double)) {
        throw new InvalidArgumentException(
            new Message('Invalid base64 formatting'),
            100
        );
    }
    unset($double);
}

/**
 * @param string $base64 A base64 encoded string
 * @param string $filename Filename or stream to store decoded base64
 *
 * @throws FilesystemException
 * @throws StreamException
 * @throws RuntimeException
 */
function storeDecodedBase64(string $base64, string $filename): void
{
    $filter = 'convert.base64-decode';
    $fh = fopen($filename, 'w');
    stream_filter_append($fh, $filter, STREAM_FILTER_WRITE);
    if (fwrite($fh, $base64) === 0) {
        throw new RuntimeException(
            (new Message('Unable to write %filter% provided string'))
                ->code('%filter%', $filter),
            1200
        );
    }
    fclose($fh);
}

function getBase64Regex(): RegexInterface
{
    return new Regex('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/');
}
