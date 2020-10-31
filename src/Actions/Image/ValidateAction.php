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

namespace Chevereto\Actions\Image;

use Chevere\Components\Action\Action;
use Chevere\Components\Message\Message;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Parameter\StringParameter;
use Chevere\Components\Regex\Regex;
use Chevere\Components\Response\ResponseSuccess;
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Interfaces\Message\MessageInterface;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use function Chevereto\Image\imageHash;
use function Chevereto\Image\imageManager;
use function Safe\md5_file;

/**
 * Validates an image against the image processing and file dimensions.
 *
 * Provides a run method returning a `ResponseSuccess` with
 * data `['image' => <\Intervention\Image\Image>, 'perceptual' => <string>, 'md5' => <string>]`.
 */
class ValidateAction extends Action
{
    private int $maxWidth;

    private int $maxHeight;

    private int $minWidth;

    private int $minHeight;

    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAddedRequired(
                (new StringParameter('filename'))
                    ->withRegex(new Regex('/^.+$/'))
            )
            ->withAddedRequired(
                (new StringParameter('maxHeight'))
                    ->withRegex(new Regex('/^\d+$/'))
            )
            ->withAddedRequired(
                (new StringParameter('maxWidth'))
                    ->withRegex(new Regex('/^\d+$/'))
            )
            ->withAddedRequired(
                (new StringParameter('minHeight'))
                    ->withRegex(new Regex('/^\d+$/'))
                    ->withDefault('16')
            )
            ->withAddedRequired(
                (new StringParameter('minWidth'))
                    ->withRegex(new Regex('/^\d+$/'))
                    ->withDefault('16')
            );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $filename = $arguments->get('filename');
        $image = imageManager()->make($filename);
        $this->maxWidth = (int) $arguments->get('maxWidth');
        $this->maxHeight = (int) $arguments->get('maxHeight');
        $this->minWidth = (int) $arguments->get('minWidth');
        $this->minHeight = (int) $arguments->get('minHeight');
        $this->assertMaxWidth($image->width());
        $this->assertMaxHeight($image->height());
        $this->assertMinWidth($image->width());
        $this->assertMinHeight($image->height());

        return new ResponseSuccess([
            'image' => $image,
            'perceptual' => imageHash()->hash($filename)->toHex(),
            'md5' => md5_file($filename)
        ]);
    }

    private function assertMaxWidth(int $width): void
    {
        if ($width > $this->maxWidth) {
            throw new InvalidArgumentException(
                $this->getMaxExceptionMessage('width', $width),
                1100
            );
        }
    }

    private function assertMaxHeight(int $height): void
    {
        if ($height > $this->maxHeight) {
            throw new InvalidArgumentException(
                $this->getMaxExceptionMessage('height', $height),
                1101
            );
        }
    }

    private function getMaxExceptionMessage(string $dimension, int $provided): MessageInterface
    {
        return (new Message('Image %dimension% %provided% exceeds the maximum allowed (%allowed%)'))
            ->code('%dimension%', $dimension)
            ->code('%provided%', (string) $provided)
            ->code('%allowed%', $this->getMaxAllowed());
    }

    private function getMaxAllowed(): string
    {
        return (string) $this->maxWidth . 'x' . (string) $this->maxHeight;
    }

    private function assertMinWidth(int $width): void
    {
        if ($width < $this->minWidth) {
            throw new InvalidArgumentException(
                $this->getMinExceptionMessage('width', $width),
                1102
            );
        }
    }

    private function assertMinHeight(int $height): void
    {
        if ($height < $this->minHeight) {
            throw new InvalidArgumentException(
                $this->getMinExceptionMessage('height', $height),
                1103
            );
        }
    }

    private function getMinExceptionMessage(string $dimension, int $provided): MessageInterface
    {
        return (new Message("Image %dimension% %provided% doesn't meet the the minimum required (%required%)"))
            ->code('%dimension%', $dimension)
            ->code('%provided%', (string) $provided)
            ->code('%required%', $this->getMinRequired());
    }

    private function getMinRequired(): string
    {
        return (string) $this->minWidth . 'x' . (string) $this->minHeight;
    }
}
