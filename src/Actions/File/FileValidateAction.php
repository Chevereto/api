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

namespace Chevereto\Actions\File;

use Chevere\Components\Action\Action;
use Chevere\Components\Message\Message;
use Chevere\Components\Parameter\IntegerParameter;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Parameter\StringParameter;
use Chevere\Components\Regex\Regex;
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use function Safe\filesize;
use function Safe\md5_file;
use function Safe\mime_content_type;
use Throwable;

/**
 * Validate file type and its size.
 *
 * Response parameters:
 *
 * ```php
 * bytes: int,
 * mime: string,
 * md5: string,
 * ```
 */
class FileValidateAction extends Action
{
    private array $mimes = [];

    private int $maxBytes = 0;

    private int $minBytes = 0;

    public function getParameters(): ParametersInterface
    {
        return (new Parameters(
            mimes : (new StringParameter())
                ->withRegex(new Regex('/^([\w]+\/[\w\-\+\.]+)+(,([\w]+\/[\w\-\+\.]+))*$/'))
                ->withDescription('Comma-separated list of allowed mime-types'),
            filepath : new StringParameter(),
        ))
            ->withAddedOptional(
                maxBytes : (new IntegerParameter())->withDefault(0),
                minBytes : (new IntegerParameter())->withDefault(0),
            );
    }

    public function getResponseParameters(): ParametersInterface
    {
        return new Parameters(
            bytes : new IntegerParameter(),
            mime : new StringParameter(),
            md5 : new StringParameter(),
        );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $this->mimes = explode(',', $arguments->getString('mimes')) ?: [];
        $this->minBytes = $arguments->has('minBytes')
            ? $arguments->getInteger('minBytes')
            : 0;
        $filepath = $arguments->getString('filepath');
        $bytes = $this->assertGetFileBytes($filepath);
        if ($arguments->has('maxBytes')) {
            $this->maxBytes = $arguments->getInteger('maxBytes');
            $this->assertMaxBytes($bytes);
        }
        $this->assertMinBytes($bytes);
        $mime = mime_content_type($filepath);
        $this->assertMime($mime);

        return $this->getResponse(
            bytes: $bytes,
            mime: $mime,
            md5: md5_file($filepath),
        );
    }

    /**
     * @codeCoverageIgnore
     */
    private function assertGetFileBytes(string $filename): int
    {
        try {
            return filesize($filename);
        } catch (Throwable $e) {
            throw new InvalidArgumentException(
                new Message($e->getMessage()),
                1000
            );
        }
    }

    private function assertMinBytes(int $bytes): void
    {
        if ($bytes < $this->minBytes) {
            throw new InvalidArgumentException(
                (new Message("Filesize (%fileSize%) doesn't meet the minimum bytes required (%required%)"))
                    ->code('%fileSize%', (string) $bytes . ' B')
                    ->code('%required%', (string) $this->minBytes . ' B'),
                1001
            );
        }
    }

    private function assertMaxBytes(int $bytes): void
    {
        if ($bytes > $this->maxBytes) {
            throw new InvalidArgumentException(
                (new Message('Filesize (%fileSize%) exceeds the maximum bytes allowed (%allowed%)'))
                    ->code('%fileSize%', (string) $bytes . ' B')
                    ->code('%allowed%', (string) $this->maxBytes . ' B'),
                1002
            );
        }
    }

    private function assertMime(string $mime): void
    {
        if (! in_array($mime, $this->mimes, true)) {
            throw new InvalidArgumentException(
                (new Message('File mime-type %type% is not allowed (allows %allowed%)'))
                    ->code('%type%', $mime)
                    ->code('%allowed%', implode(', ', $this->mimes)),
                1004
            );
        }
    }
}
