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
use Throwable;
use function Safe\filesize;
use function Safe\md5_file;
use function Safe\mime_content_type;

/**
 * Validate file type and its size.
 */
class FileValidateAction extends Action
{
    private array $extensions;

    private int $maxBytes;

    private int $minBytes;

    private ArgumentsInterface $arguments;

    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
        ->withAddedRequired(
            (new StringParameter('extensions'))
                ->withRegex(new Regex('/^[\w]+(,[\w]+)*$/'))
                ->withDescription('Comma-separated list of allowed file extensions')
        )
            ->withAddedRequired(
                new StringParameter('filename')
            )
            ->withAddedOptional(
                new IntegerParameter('maxBytes')
            )
            ->withAddedOptional(
                new IntegerParameter('minBytes')
            );
    }

    public function getResponseDataParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAddedRequired(new IntegerParameter('bytes'))
            ->withAddedRequired(new StringParameter('mime'))
            ->withAddedRequired(new StringParameter('md5'));
    }

    public function run(array $arguments): ResponseSuccessInterface
    {
        $this->arguments = $this->getArguments($arguments);
        $this->extensions = explode(',', $this->arguments->getString('extensions')) ?: [];
        $this->minBytes = $this->arguments->has('minBytes')
            ? $this->arguments->getInteger('minBytes')
            : 0;
        $filename = $this->arguments->getString('filename');
        $bytes = $this->assertGetFileBytes($filename);
        if ($this->arguments->has('maxBytes')) {
            $this->maxBytes = $this->arguments->getInteger('maxBytes');
            $this->assertMaxBytes($bytes);
        }
        $this->assertMinBytes($bytes);
        $mime = mime_content_type($filename);
        $mimes = new MimeTypes;
        $extensions = $mimes->getAllExtensions($mime);
        $this->assertExtension($extensions);
        $data = [
            'bytes' => $bytes,
            'mime' => $mime,
            'md5' => md5_file($filename)
        ];

        return $this->getResponseSuccess($data);
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

    private function assertMinBytes(int $bytes)
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
        if (!array_intersect($extensions, $this->extensions)) {
            throw new InvalidArgumentException(
                (new Message('File extension %extension% is not allowed (allows %allowed%)'))
                    ->code('%extension%', implode(', ', $extensions))
                    ->code('%allowed%', implode(', ', $this->extensions)),
                1004
            );
        }
    }
}
