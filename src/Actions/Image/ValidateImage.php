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
use Chevere\Components\Parameter\ParameterRequired;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Regex\Regex;
use Chevere\Components\Response\ResponseFailure;
use Chevere\Components\Response\ResponseSuccess;
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Interfaces\Message\MessageInterface;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Throwable;

class ValidateImage extends Action
{
    private Image $image;

    private int $maxWidth;

    private int $maxHeight;

    private int $minWidth;

    private int $minHeight;

    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAdded(
                (new ParameterRequired('filename'))
                    ->withRegex(new Regex('/^.+$/'))
            )
            ->withAdded(
                (new ParameterRequired('maxWidth'))
                    ->withRegex(new Regex('/^\d+$/'))
            )
            ->withAdded(
                (new ParameterRequired('maxHeight'))
                    ->withRegex(new Regex('/^\d+$/'))
            )
            ->withAdded(
                (new ParameterRequired('minWidth'))
                    ->withRegex(new Regex('/^\d+$/'))
                    ->withDefault('16')
            )
            ->withAdded(
                (new ParameterRequired('minHeight'))
                    ->withRegex(new Regex('/^\d+$/'))
                    ->withDefault('16')
            );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        try {
            $filename = $arguments->get('filename');
            $manager = new ImageManager(['driver' => 'Imagick']);
            $this->image = $manager->make($filename);
            $this->maxWidth = (int) $arguments->get('maxWidth');
            $this->maxHeight = (int) $arguments->get('maxHeight');
            $this->minWidth = (int) $arguments->get('minWidth');
            $this->minHeight = (int) $arguments->get('minHeight');
            $this->assertMaxWidth($this->image->width());
            $this->assertMaxHeight($this->image->height());
            $this->assertMinWidth($this->image->width());
            $this->assertMinHeight($this->image->height());
        } catch (Throwable $e) {
            return new ResponseFailure(
                [
                    'message' => (new Message('%message% for file %path%'))
                        ->strong('%path%', $filename)
                        ->strtr('%message%', $e->getMessage())
                        ->toString(),
                    'code' => $e->getCode()
                ]
            );
        }

        return new ResponseSuccess([
            'width' => $this->image->width(),
            'height' => $this->image->height(),
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
