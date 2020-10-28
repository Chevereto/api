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
use Chevere\Components\Parameter\ParameterRequired;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Regex\Regex;
use Chevere\Components\Response\ResponseSuccess;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Imagick;

class StripImageMetaAction extends Action
{
    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAdded(
                (new ParameterRequired('filename'))
                    ->withRegex(new Regex('/^.+$/'))
            );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $filename = $arguments->get('filename');
        $image = new Imagick($filename);
        $profiles = $image->getImageProfiles('icc', true);
        $image->stripImage();
        if (!empty($profiles)) {
            $image->profileImage('icc', $profiles['icc']); //@codeCoverageIgnore
        }
        $image->writeImage($filename);
        $image->clear();
        $image->destroy();

        return new ResponseSuccess([]);
    }
}
