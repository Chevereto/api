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
use Chevere\Components\Parameter\ArrayParameter;
use Chevere\Components\Parameter\Parameter;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Type\Type;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseSuccessInterface;
use Intervention\Image\Image;
use JeroenDesloovere\XmpMetadataExtractor\XmpMetadataExtractor;

/**
 * Fetch image metadata.
 */
class ImageFetchMetaAction extends Action
{
    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAddedRequired(
                new Parameter('image', new Type(Image::class))
            );
    }

    public function getResponseDataParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAddedRequired(
                new ArrayParameter('exif'),
                new ArrayParameter('iptc'),
                new ArrayParameter('xmp'),
            );
    }

    public function run(array $arguments): ResponseSuccessInterface
    {
        $arguments = $this->getArguments($arguments);
        /**
         * @var Image $image
         */
        $image = $arguments->get('image');
        $data = array_fill_keys(['exif', 'iptc', 'xmp'], []);
        $data['exif'] = $image->exif() ?? [];
        $data['iptc'] = $image->iptc() ?? [];
        $xmpDataExtractor = new XmpMetadataExtractor();
        $data['xmp'] = $xmpDataExtractor->extractFromFile($image->basePath());

        return $this->getResponseSuccess($data);
    }
}
