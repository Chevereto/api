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

namespace Chevereto\Actions\Video;

use Chevere\Components\Action\Action;
use Chevere\Components\Message\Message;
use Chevere\Components\Parameter\IntegerParameter;
use Chevere\Components\Parameter\Parameter;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Parameter\StringParameter;
use Chevere\Components\Type\Type;
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Interfaces\Message\MessageInterface;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use FFMpeg\FFProbe;
use FFMpeg\FFProbe\DataMapping\Format;
use FFMpeg\FFProbe\DataMapping\Stream;
use Throwable;

/**
 * Validates a video against the video processing and file dimensions.
 */
class ValidateMediaAction extends Action
{
    private int $maxHeight = 0;

    private int $maxLength = 0;

    private int $maxWidth = 0;

    private int $minHeight = 0;

    private int $minLength = 0;

    private int $minWidth = 0;

    public function getParameters(): ParametersInterface
    {
        return (new Parameters())
            ->withAddedRequired(
                filename: new StringParameter(),
                maxHeight: new IntegerParameter(),
                maxWidth: new IntegerParameter(),
                maxLength: (new IntegerParameter())
                    ->withDefault(3600),
                minHeight: (new IntegerParameter())
                    ->withDefault(16),
                minWidth: (new IntegerParameter())
                    ->withDefault(16),
                minLength: (new IntegerParameter())
                    ->withDefault(5),
            );
    }

    public function getResponseDataParameters(): ParametersInterface
    {
        return (new Parameters())
            ->withAddedRequired(
                format: new Parameter(new Type(Format::class)),
                stream: new Parameter(new Type(Stream::class)),
            );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $filename = $arguments->getString('filename');
        $probe = FFProbe::create();
        $this->assertValidMedia($probe, $filename);
        $format = $this->assertGetFormat($probe, $filename);
        $stream = $this->assertGetStream($probe, $filename);
        $this->assertValidVideo($stream, $filename);
        $this->maxHeight = $arguments->getInteger('maxHeight');
        $this->maxLength = $arguments->getInteger('maxLength');
        $this->maxWidth = $arguments->getInteger('maxWidth');
        $this->minHeight = $arguments->getInteger('minHeight');
        $this->minLength = $arguments->getInteger('minLength');
        $this->minWidth = $arguments->getInteger('minWidth');
        $this->assertHeight($stream->get('height'));
        $this->assertLength((float) $format->get('duration'));
        $this->assertWidth($stream->get('width'));

        return $this->getResponse(
            stream: $stream,
            format: $format,
        );
    }

    private function assertValidMedia(FFProbe $probe, string $filename): void
    {
        if (! $probe->isValid($filename)) {
            throw new InvalidArgumentException(
                $this->getManagerExceptionMessage($filename),
                1000
            );
        }
    }

    private function assertValidVideo(Stream $stream, string $filename): void
    {
        if (! $stream->isVideo()) {
            throw new InvalidArgumentException(
                $this->getManagerExceptionMessage($filename),
                1000
            );
        }
    }

    private function assertGetFormat(FFProbe $probe, string $filename): Format
    {
        try {
            return $probe->format($filename);
        }
        // @codeCoverageIgnoreStart
        catch (Throwable $e) {
            throw new InvalidArgumentException(
                $this->getManagerExceptionMessage($filename),
                100
            );
        }
        // @codeCoverageIgnoreEnd
    }

    private function assertGetStream(FFProbe $probe, string $filename): Stream
    {
        try {
            return $probe->streams($filename)->first();
        }
        // @codeCoverageIgnoreStart
        catch (Throwable $e) {
            throw new InvalidArgumentException(
                $this->getManagerExceptionMessage($filename),
                101
            );
        }
        // @codeCoverageIgnoreEnd
    }

    private function assertHeight(int $height): void
    {
        if ($height < $this->minHeight) {
            throw new InvalidArgumentException(
                $this->getMinDimensionExceptionMessage('length', $height),
                1001
            );
        }
        if ($height > $this->maxHeight) {
            throw new InvalidArgumentException(
                $this->getMaxDimensionExceptionMessage('height', $height),
                1002
            );
        }
    }

    private function assertLength(float $length): void
    {
        if ($length < $this->minLength) {
            throw new InvalidArgumentException(
                (new Message("Video length %provided% doesn't meet the the minimum required of %required%"))
                    ->code('%provided%', (string) $length)
                    ->code('%required%', (string) $this->minLength . ' seconds'),
                1003
            );
        }
        if ($length > $this->maxLength) {
            throw new InvalidArgumentException(
                (new Message('Video length %provided% exceeds the maximum allowed of %allowed%'))
                    ->code('%provided%', (string) $length)
                    ->code('%allowed%', (string) $this->maxLength . ' seconds'),
                1004
            );
        }
    }

    private function assertWidth(int $width): void
    {
        if ($width < $this->minWidth) {
            throw new InvalidArgumentException(
                $this->getMinDimensionExceptionMessage('width', $width),
                1005
            );
        }
        if ($width > $this->maxWidth) {
            throw new InvalidArgumentException(
                $this->getMaxDimensionExceptionMessage('width', $width),
                1006
            );
        }
    }

    private function getManagerExceptionMessage(string $filename): MessageInterface
    {
        return (new Message("Filename provided %filename% doesn't validate according to %manager%"))
            ->code('%filename%', $filename)
            ->code('%manager%', FFProbe::class);
    }

    private function getMaxDimensionExceptionMessage(string $dimension, int $provided): MessageInterface
    {
        return (new Message('Video %dimension% %provided% exceeds the maximum allowed (%allowed%)'))
            ->code('%dimension%', $dimension)
            ->code('%provided%', (string) $provided)
            ->code('%allowed%', (string) $this->maxWidth . 'x' . (string) $this->maxHeight);
    }

    private function getMinDimensionExceptionMessage(string $dimension, int $provided): MessageInterface
    {
        return (new Message("Video %dimension% %provided% doesn't meet the the minimum required (%required%)"))
            ->code('%dimension%', $dimension)
            ->code('%provided%', (string) $provided)
            ->code('%required%', (string) $this->minWidth . 'x' . (string) $this->minHeight);
    }
}
