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

namespace Chevereto\Actions\File;

use Chevere\Components\Action\Action;
use Chevere\Components\Parameter\Parameter;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Parameter\StringParameter;
use Chevere\Components\Regex\Regex;
use Chevere\Components\Type\Type;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Chevereto\Components\Storage;

/**
 * Upload the filename to the target storage.
 */
class FileUploadAction extends Action
{
    public function getParameters(): ParametersInterface
    {
        return (new Parameters())
            ->withAddedRequired(
                filename: new StringParameter(),
                naming: (new StringParameter())
                    ->withRegex(new Regex('/^(original|random|mixed|id)$/'))
                    ->withDefault('original'),
                originalName: (new StringParameter())
                    ->withRegex(new Regex('/^.+\.[a-zA-Z]{3}$/')),
                storage: new Parameter(new Type(Storage::class)),
                uploadPath: new StringParameter(),
            );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $filename = $arguments->getString('filename');
        $naming = $arguments->getString('naming');
        $originalName = $arguments->getString('originalName');
        /** @var Storage $storage */
        $storage = $arguments->get('storage');

        return $this->getResponse(path: '123');
    }
}
