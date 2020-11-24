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
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Interfaces\Message\MessageInterface;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use function Chevereto\Image\imageManager;
use function Safe\md5_file;

/**
 * Validates a video against the video processing and file dimensions.
 */
class ValidateMediaAction extends Action
{
    private int $maxWidth;

    private int $maxHeight;

    private int $minWidth;

    private int $minHeight;

    private int $minLength;

    private int $maxLength;

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
            ->withAddedRequired(new Parameter('video', VIDEO::class))
            ->withAddedRequired(new StringParameter('md5'));
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $filename = $arguments->get('filename');
        $video = imageManager()->make($filename);
        $this->maxWidth = (int) $arguments->get('maxWidth');
        $this->maxHeight = (int) $arguments->get('maxHeight');
        $this->maxLength = (int) $arguments->get('maxLength');
        $this->minWidth = (int) $arguments->get('minWidth');
        $this->minHeight = (int) $arguments->get('minHeight');
        $this->minLength = (int) $arguments->get('minLength');
        $this->assertMaxWidth($video->width());
        $this->assertMaxHeight($video->height());
        $this->assertMaxLength($video->length());
        $this->assertMinWidth($video->width());
        $this->assertMinHeight($video->height());
        $this->assertMinLength($video->length());

        return new ResponseSuccess([
            'video' => $video,
            'md5' => md5_file($filename)
        ]);
    }

    private function assertMaxWidth(int $width): void
    {
        if ($width > $this->maxWidth) {
            throw new InvalidArgumentException(
                $this->getMaxDimensionExceptionMessage('width', $width),
                1100
            );
        }
    }

    private function assertMaxHeight(int $height): void
    {
        if ($height > $this->maxHeight) {
            throw new InvalidArgumentException(
                $this->getMaxDimensionExceptionMessage('height', $height),
                1101
            );
        }
    }

    private function assertMaxLength(int $length): void
    {
        if ($length > $this->maxLength) {
            throw new InvalidArgumentException(
                $this->getMaxLengthExceptionMessage('length', $length),
                1101
            );
        }
    }

    private function getMaxDimensionExceptionMessage(string $dimension, int $provided): MessageInterface
    {
        return (new Message('Video %dimension% %provided% exceeds the maximum allowed (%allowed%)'))
            ->code('%dimension%', $dimension)
            ->code('%provided%', (string) $provided)
            ->code('%allowed%', $this->getMaxDimensionAllowed());
    }

    private function getMaxLengthExceptionMessage(string $dimension, int $provided): MessageInterface
    {
        return (new Message('Video %dimension% %provided% exceeds the maximum allowed of %allowed%'))
            ->code('%dimension%', $dimension)
            ->code('%provided%', (string) $provided)
            ->code('%allowed%', (string) $this->maxLength . ' seconds');
    }

    private function getMaxDimensionAllowed(): string
    {
        return (string) $this->maxWidth . 'x' . (string) $this->maxHeight;
    }

    private function assertMinWidth(int $width): void
    {
        if ($width < $this->minWidth) {
            throw new InvalidArgumentException(
                $this->getMinDimensionExceptionMessage('width', $width),
                1102
            );
        }
    }

    private function assertMinHeight(int $height): void
    {
        if ($height < $this->minHeight) {
            throw new InvalidArgumentException(
                $this->getMinDimensionExceptionMessage('length', $height),
                1103
            );
        }
    }

    private function assertMinLength(int $length): void
    {
        if ($length < $this->minLength) {
            throw new InvalidArgumentException(
                $this->getMinLengthExceptionMessage('length', $length),
                1104
            );
        }
    }

    private function getMinDimensionExceptionMessage(string $dimension, int $provided): MessageInterface
    {
        return (new Message("Video %dimension% %provided% doesn't meet the the minimum required (%required%)"))
            ->code('%dimension%', $dimension)
            ->code('%provided%', (string) $provided)
            ->code('%required%', $this->getMinDimensionRequired());
    }

    private function getMinLengthExceptionMessage(string $dimension, int $provided): MessageInterface
    {
        return (new Message("Video %dimension% %provided% doesn't meet the the minimum required of %required%"))
            ->code('%dimension%', $dimension)
            ->code('%provided%', (string) $provided)
            ->code('%required%', (string) $this->minLength . ' seconds');
    }

    private function getMinDimensionRequired(): string
    {
        return (string) $this->minWidth . 'x' . (string) $this->minHeight;
    }
}
