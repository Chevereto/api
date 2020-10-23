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
use Chevere\Components\Parameter\ParameterOptional;
use Chevere\Components\Parameter\ParameterRequired;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Regex\Regex;
use Chevere\Components\Response\ResponseFailure;
use Chevere\Components\Response\ResponseSuccess;
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Mimey\MimeTypes;
use function Chevere\Components\Filesystem\fileForString;
use function Safe\mime_content_type;

class ValidateFile extends Action
{
    private array $extensions;

    private int $minBytes;

    private int $maxBytes;

    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAdded(
                (new ParameterRequired('filename'))
                    ->withRegex(new Regex('/^.+$/'))
            )
            ->withAdded(
                (new ParameterRequired('extensions'))
                    ->withDescription('Comma-separated list of allowed file extensions')
                    ->withRegex(new Regex('/^[\w]+(,[\w]+)*$/'))
            )
            ->withAdded(
                (new ParameterOptional('maxBytes'))
                    ->withRegex(new Regex('/^\d+$/'))
            )
            ->withAdded(
                (new ParameterOptional('minBytes'))
                    ->withRegex(new Regex('/^\d+$/'))
                    ->withDefault('0')
            );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $this->extensions = explode(',', $arguments->get('extensions'));
        $this->minBytes = (int) $arguments->get('minBytes');
        try {
            $file = fileForString($arguments->get('filename'));
            $bytes = $file->getSize();
            $this->assertMinBytes($bytes);
            if ($arguments->has('maxBytes')) {
                $this->maxBytes = (int) $arguments->get('maxBytes');
                $this->assertMaxBytes($bytes);
            }
            $mime = mime_content_type($file->path()->absolute());
            $mimes = new MimeTypes;
            $extension = $mimes->getExtension($mime) ?? '';
            $this->assertExtension($extension);
        } catch (InvalidArgumentException $e) {
            return new ResponseFailure(
                [
                    'message' => (new Message('File %path% %message%'))
                        ->strong('%path%', $file->path()->absolute())
                        ->strtr('%message%', $e->getMessage())
                        ->toString(),
                    'code' => $e->getCode()
                ]
            );
        }

        return new ResponseSuccess([
            'filename' => $arguments->get('filename'),
            'bytes' => $bytes,
            'mime' => $mime,
            'extension' => $extension
        ]);
    }

    private function assertMinBytes(int $bytes)
    {
        if ($bytes < $this->minBytes) {
            throw new InvalidArgumentException(
                (new Message("size (%fileSize%) doesn't meet the minimum bytes required (%allowed%)"))
                    ->code('%fileSize%', (string) $bytes . ' B')
                    ->code('%allowed%', (string) $this->minBytes . ' B'),
                1100
            );
        }
    }

    private function assertMaxBytes(int $bytes): void
    {
        if ($bytes > $this->maxBytes) {
            throw new InvalidArgumentException(
                (new Message('size (%fileSize%) exceeds the maximum bytes allowed (%allowed%)'))
                    ->code('%fileSize%', (string) $bytes . ' B')
                    ->code('%allowed%', (string) $this->maxBytes . ' B'),
                1101
            );
        }
    }

    private function assertExtension(string $extension): void
    {
        if ($extension === '') {
            // @codeCoverageIgnoreStart
            throw new InvalidArgumentException(
                new Message('unable to detect extension'),
                1102
            );
            // @codeCoverageIgnoreEnd
        }
        if (!in_array($extension, $this->extensions)) {
            throw new InvalidArgumentException(
                (new Message('extension %extension% is not in the list of allowed extensions: %allowed%'))
                    ->code('%extension%', $extension)
                    ->code('%allowed%', implode(', ', $this->extensions)),
                1103
            );
        }
    }
}
