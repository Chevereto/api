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
use Chevere\Components\Parameter\IntegerParameter;
use Chevere\Components\Parameter\Parameter;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Parameter\StringParameter;
use Chevere\Components\Type\Type;
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Exceptions\Core\OutOfBoundsException;
use Chevere\Exceptions\Core\TypeException;
use Chevere\Interfaces\Message\MessageInterface;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use function Chevereto\Image\imageHash;
use function Chevereto\Image\imageManager;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Throwable;

/**
 * Validates an image against the image processing and image dimensions.
 */
class ImageValidateMediaAction extends Action
{
    private int $width = 0;

    private int $height = 0;

    private int $maxWidth = 0;

    private int $maxHeight = 0;

    private int $minWidth = 0;

    private int $minHeight = 0;

    public function getParameters(): ParametersInterface
    {
        return (new Parameters())
            ->withAddedRequired(
                filename: new StringParameter(),
                maxHeight: new IntegerParameter(),
                maxWidth: new IntegerParameter(),
                minHeight: new IntegerParameter(),
                minWidth: new IntegerParameter(),
            );
    }

    public function getResponseDataParameters(): ParametersInterface
    {
        return (new Parameters())
            ->withAddedRequired(
                image: new Parameter(new Type(Image::class)),
                perceptual: new StringParameter(),
            );
    }

    /**
     * @throws InvalidArgumentException
     * @throws OutOfBoundsException
     * @throws TypeException
     */
    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $filename = $arguments->getString('filename');
        $image = $this->assertGetImage($filename);
        $this->width = $image->width();
        $this->height = $image->height();
        $this->maxWidth = $arguments->getInteger('maxWidth');
        $this->maxHeight = $arguments->getInteger('maxHeight');
        $this->minWidth = $arguments->getInteger('minWidth');
        $this->minHeight = $arguments->getInteger('minHeight');
        $this->assertMinHeight();
        $this->assertMaxHeight();
        $this->assertMinWidth();
        $this->assertMaxWidth();

        return $this->getResponse(
            image: $image,
            perceptual: imageHash()->hash($filename)->toHex()
        );
    }

    private function assertGetImage(string $filename): Image
    {
        try {
            return imageManager()->make($filename);
        } catch (Throwable $e) {
            throw new InvalidArgumentException(
                (new Message("Filename provided can't be handled by %manager%: %message%"))
                    ->code('%manager%', ImageManager::class)
                    ->strtr('%message%', $e->getMessage()),
                1000
            );
        }
    }

    private function assertMinHeight(): void
    {
        if ($this->height < $this->minHeight) {
            throw new InvalidArgumentException(
                $this->getMinExceptionMessage('height', $this->height),
                1001
            );
        }
    }

    private function assertMaxHeight(): void
    {
        if ($this->height > $this->maxHeight) {
            throw new InvalidArgumentException(
                $this->getMaxExceptionMessage('height', $this->height),
                1002
            );
        }
    }

    private function assertMinWidth(): void
    {
        if ($this->width < $this->minWidth) {
            throw new InvalidArgumentException(
                $this->getMinExceptionMessage('width', $this->width),
                1003
            );
        }
    }

    private function assertMaxWidth(): void
    {
        if ($this->width > $this->maxWidth) {
            throw new InvalidArgumentException(
                $this->getMaxExceptionMessage('width', $this->width),
                1004
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
}
