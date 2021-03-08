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
use Chevere\Components\Parameter\ObjectParameter;
use Chevere\Components\Parameter\Parameters;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Intervention\Image\Image;

/**
 * Fix the image orientation based on Exif Orientation (if any, if needed).
 */
class ImageFixOrientationAction extends Action
{
    public function getParameters(): ParametersInterface
    {
        return new Parameters(
            image: new ObjectParameter(Image::class)
        );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        /** @var Image $image */
        $image = $arguments->get('image');
        $image->orientate()->save();

        return $this->getResponse();
    }
}
