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
use Chevere\Interfaces\Response\ResponseSuccessInterface;
use Mimey\MimeTypes;
use function Safe\filesize;
use function Safe\md5_file;
use function Safe\mime_content_type;
use Throwable;

/**
 * Validate file type and its size.
 */
class FileValidateAction extends Action
{
    private array $extensions = [];

    private int $maxBytes = 0;

    private int $minBytes = 0;

    public function getParameters(): ParametersInterface
    {
        return (new Parameters())
            ->withAddedRequired(
                extensions : (new StringParameter())
                    ->withRegex(new Regex('/^[\w]+(,[\w]+)*$/'))
                    ->withDescription('Comma-separated list of allowed file extensions'),
                filename : new StringParameter(),
            )
            ->withAddedOptional(
                maxBytes : new IntegerParameter(),
                minBytes : new IntegerParameter(),
            );
    }

    public function getResponseDataParameters(): ParametersInterface
    {
        return (new Parameters())
            ->withAddedRequired(
                bytes : new IntegerParameter(),
                mime : new StringParameter(),
                md5 : new StringParameter(),
            );
    }

    public function run(ArgumentsInterface $arguments): ResponseSuccessInterface
    {
        $this->extensions = explode(',', $arguments->getString('extensions')) ?: [];
        $this->minBytes = $arguments->has('minBytes')
            ? $arguments->getInteger('minBytes')
            : 0;
        $filename = $arguments->getString('filename');
        $bytes = $this->assertGetFileBytes($filename);
        if ($arguments->has('maxBytes')) {
            $this->maxBytes = $arguments->getInteger('maxBytes');
            $this->assertMaxBytes($bytes);
        }
        $this->assertMinBytes($bytes);
        $mime = mime_content_type($filename);
        $mimes = new MimeTypes();
        $extensions = $mimes->getAllExtensions($mime);
        $this->assertExtension($extensions);

        return $this->getResponseSuccess(
            [
                'bytes' => $bytes,
                'mime' => $mime,
                'md5' => md5_file($filename),
            ]
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

    private function assertExtension(array $extensions): void
    {
        if ($extensions === []) {
            // @codeCoverageIgnoreStart
            throw new InvalidArgumentException(
                new Message('Unable to detect file extension'),
                1003
            );
            // @codeCoverageIgnoreEnd
        }
        if (! array_intersect($extensions, $this->extensions)) {
            throw new InvalidArgumentException(
                (new Message('File extension %extension% is not allowed (allows %allowed%)'))
                    ->code('%extension%', implode(', ', $extensions))
                    ->code('%allowed%', implode(', ', $this->extensions)),
                1004
            );
        }
    }
}
