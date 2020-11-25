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
use Chevere\Components\Response\ResponseSuccess;
use Chevere\Components\Type\Type;
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Interfaces\Message\MessageInterface;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Intervention\Image\Image;
use function Chevereto\Image\imageHash;
use function Chevereto\Image\imageManager;
use function Safe\md5_file;

/**
 * Validates an image against the image processing and image dimensions.
 */
class ValidateMediaAction extends Action
{
    private int $maxWidth;

    private int $maxHeight;

    private int $minWidth;

    private int $minHeight;

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
                new IntegerParameter('minHeight')
            )
            ->withAddedRequired(
                new IntegerParameter('minWidth')
            );
    }

    public function getResponseDataParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAddedRequired(new Parameter('image', new Type(Image::class)))
            ->withAddedRequired(new StringParameter('perceptual'));
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $filename = $arguments->getString('filename');
        $image = imageManager()->make($filename);
        $this->maxWidth = $arguments->getInteger('maxWidth');
        $this->maxHeight = $arguments->getInteger('maxHeight');
        $this->minWidth = $arguments->getInteger('minWidth');
        $this->minHeight = $arguments->getInteger('minHeight');
        $this->assertMaxWidth($image->width());
        $this->assertMaxHeight($image->height());
        $this->assertMinWidth($image->width());
        $this->assertMinHeight($image->height());
        $data = [
            'image' => $image,
            'perceptual' => imageHash()->hash($filename)->toHex(),
        ];
        $this->assertResponseDataParameters($data);

        return new ResponseSuccess($data);
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
