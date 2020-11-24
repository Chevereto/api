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
use Chevere\Components\Response\ResponseSuccess;
use Chevere\Components\Type\Type;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Intervention\Image\Image;
use JeroenDesloovere\XmpMetadataExtractor\XmpMetadataExtractor;

/**
 * Fetch image metadata.
 */
class FetchMetaAction extends Action
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
            ->withAddedRequired(new ArrayParameter('exif'))
            ->withAddedRequired(new ArrayParameter('iptc'))
            ->withAddedRequired(new ArrayParameter('xmp'));
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        /**
         * @var Image $image
         */
        $image = $arguments->get('image');
        $data = array_fill_keys(['exif', 'iptc', 'xmp'], []);
        $data['exif'] = $image->exif() ?? [];
        $data['iptc'] = $image->iptc() ?? [];
        $xmpDataExtractor = new XmpMetadataExtractor();
        $data['xmp'] = $xmpDataExtractor->extractFromFile($image->basePath());
        $this->assertResponseDataParameters($data);

        return new ResponseSuccess($data);
    }
}
