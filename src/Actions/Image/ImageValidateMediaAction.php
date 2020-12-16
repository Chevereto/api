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
use Chevere\Interfaces\Response\ResponseSuccessInterface;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Throwable;
use function Chevereto\Image\imageHash;
use function Chevereto\Image\imageManager;

/**
 * Validates an image against the image processing and image dimensions.
 */
class ImageValidateMediaAction extends Action
{
    private int $maxWidth;

    private int $maxHeight;

    private int $minWidth;

    private int $minHeight;

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
    public function run(ArgumentsInterface $arguments): ResponseSuccessInterface
    {
        $filename = $arguments->getString('filename');
        $image = $this->assertGetImage($filename);
        $this->maxWidth = $arguments->getInteger('maxWidth');
        $this->maxHeight = $arguments->getInteger('maxHeight');
        $this->minWidth = $arguments->getInteger('minWidth');
        $this->minHeight = $arguments->getInteger('minHeight');
        $this->assertHeight($image->height());
        $this->assertWidth($image->width());
        $data = [
            'image' => $image,
            'perceptual' => imageHash()->hash($filename)->toHex(),
        ];

        return $this->getResponseSuccess($data);
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

    private function assertHeight(int $height): void
    {
        if ($height < $this->minHeight) {
            throw new InvalidArgumentException(
                $this->getMinExceptionMessage('height', $height),
                1001
            );
        }
        if ($height > $this->maxHeight) {
            throw new InvalidArgumentException(
                $this->getMaxExceptionMessage('height', $height),
                1002
            );
        }
    }

    private function assertWidth(int $width): void
    {
        if ($width < $this->minWidth) {
            throw new InvalidArgumentException(
                $this->getMinExceptionMessage('width', $width),
                1003
            );
        }
        if ($width > $this->maxWidth) {
            throw new InvalidArgumentException(
                $this->getMaxExceptionMessage('width', $width),
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
