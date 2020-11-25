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
use Chevere\Components\Response\ResponseSuccess;
use Chevere\Components\Type\Type;
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Interfaces\Message\MessageInterface;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Exception;
use FFMpeg\FFProbe;
use FFMpeg\FFProbe\DataMapping\Stream;
use Throwable;

/**
 * Validates a video against the video processing and file dimensions.
 */
class ValidateMediaAction extends Action
{
    private int $maxHeight;

    private int $maxLength;

    private int $maxWidth;

    private int $minHeight;

    private int $minLength;

    private int $minWidth;

    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAddedRequired(
                new StringParameter('filename')
            )
            ->withAddedRequired(
                new IntegerParameter('maxHeight')
            )
            ->withAddedRequired(
                new IntegerParameter('maxWidth')
            )
            ->withAddedRequired(
                (new IntegerParameter('maxLength'))
                    ->withDefault(3600)
            )
            ->withAddedRequired(
                (new IntegerParameter('minHeight'))
                    ->withDefault(16)
            )
            ->withAddedRequired(
                (new IntegerParameter('minWidth'))
                    ->withDefault(16)
            )
            ->withAddedRequired(
                (new IntegerParameter('minLength'))
                    ->withDefault(5)
            );
    }

    public function getResponseDataParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAddedRequired(new Parameter('video', new Type(Stream::class)));
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $filename = $arguments->getString('filename');
        $stream = $this->assertGetStream($filename);
        $this->maxHeight = $arguments->getInteger('maxHeight');
        $this->maxLength = $arguments->getInteger('maxLength');
        $this->maxWidth = $arguments->getInteger('maxWidth');
        $this->minHeight = $arguments->getInteger('minHeight');
        $this->minLength = $arguments->getInteger('minLength');
        $this->minWidth = $arguments->getInteger('minWidth');
        $this->assertWidth($stream->get('width'));
        $this->assertLength((float) $stream->get('duration'));
        $this->assertHeight($stream->get('height'));
        $data = [
            'video' => $stream,
        ];
        $this->assertResponseDataParameters($data);

        return new ResponseSuccess($data);
    }

    private function assertGetStream(string $filename): Stream
    {
        $ffprobe = FFProbe::create();
        try {
            $format = $ffprobe->format($filename);
            $stream = $ffprobe->streams($filename)->videos()->first();
            if ($format->get('format_name') === 'image2' || !$stream->isVideo()) {
                throw new Exception;
            }
        } catch (Throwable $e) {
            throw new InvalidArgumentException(
                (new Message("Filename provided %filename% doesn't validate according to %manager%"))
                    ->code('%filename%', $filename)
                    ->code('%manager%', FFProbe::class),
                1000
            );
        }

        return $stream;
    }

    private function assertWidth(int $width): void
    {
        if ($width < $this->minWidth) {
            throw new InvalidArgumentException(
                $this->getMinDimensionExceptionMessage('width', $width),
                1100
            );
        }
        if ($width > $this->maxWidth) {
            throw new InvalidArgumentException(
                $this->getMaxDimensionExceptionMessage('width', $width),
                1101
            );
        }
    }

    private function assertHeight(int $height): void
    {
        if ($height < $this->minHeight) {
            throw new InvalidArgumentException(
                $this->getMinDimensionExceptionMessage('length', $height),
                1102
            );
        }
        if ($height > $this->maxHeight) {
            throw new InvalidArgumentException(
                $this->getMaxDimensionExceptionMessage('height', $height),
                1103
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
                1104
            );
        }
        if ($length > $this->maxLength) {
            throw new InvalidArgumentException(
                (new Message('Video length %provided% exceeds the maximum allowed of %allowed%'))
                    ->code('%provided%', (string) $length)
                    ->code('%allowed%', (string) $this->maxLength . ' seconds'),
                1105
            );
        }
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
