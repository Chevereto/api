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
use Chevere\Components\Parameter\Parameter;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Regex\Regex;
use Chevere\Components\Response\ResponseSuccess;
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Mimey\MimeTypes;
use function Safe\filesize;
use function Safe\mime_content_type;

class ValidateFileAction extends Action
{
    private array $extensions;

    private int $maxBytes;

    private int $minBytes;

    private ArgumentsInterface $arguments;

    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAddedRequired(
                (new Parameter('filename'))
                    ->withRegex(new Regex('/^.+$/'))
            )
            ->withAddedRequired(
                (new Parameter('extensions'))
                    ->withDescription('Comma-separated list of allowed file extensions')
                    ->withRegex(new Regex('/^[\w]+(,[\w]+)*$/'))
            )
            ->withAddedOptional(
                (new Parameter('maxBytes'))
                    ->withRegex(new Regex('/^\d+$/'))
            )
            ->withAddedOptional(
                (new Parameter('minBytes'))
                    ->withRegex(new Regex('/^\d+$/'))
                    ->withDefault('0')
            );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $this->arguments = $arguments;
        $this->extensions = explode(',', $this->arguments->get('extensions')) ?: [];
        $this->minBytes = (int) $this->arguments->get('minBytes');
        $filename = $this->arguments->get('filename');
        $bytes = filesize($filename);
        if ($this->arguments->has('maxBytes')) {
            $this->maxBytes = (int) $this->arguments->get('maxBytes');
            $this->assertMaxBytes($bytes);
        }
        $this->assertMinBytes($bytes);
        $mime = mime_content_type($filename);
        $mimes = new MimeTypes;
        $extensions = $mimes->getAllExtensions($mime);
        $this->assertExtension($extensions);

        return new ResponseSuccess([
            'bytes' => $bytes,
            'mime' => $mime,
        ]);
    }

    private function assertMaxBytes(int $bytes): void
    {
        if ($bytes > $this->maxBytes) {
            throw new InvalidArgumentException(
                (new Message('Filesize (%fileSize%) exceeds the maximum bytes allowed (%allowed%)'))
                    ->code('%fileSize%', (string) $bytes . ' B')
                    ->code('%allowed%', (string) $this->maxBytes . ' B'),
                1100
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
                1101
            );
        }
    }

    private function assertExtension(array $extensions): void
    {
        if ($extensions === []) {
            // @codeCoverageIgnoreStart
            throw new InvalidArgumentException(
                new Message('Unable to detect file extension'),
                1102
            );
            // @codeCoverageIgnoreEnd
        }
        if (!array_intersect($extensions, $this->extensions)) {
            throw new InvalidArgumentException(
                (new Message('File extension %extension% is not allowed (allows %allowed%)'))
                    ->code('%extension%', implode(', ', $extensions))
                    ->code('%allowed%', implode(', ', $this->extensions)),
                1103
            );
        }
    }
}
