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
use Chevere\Components\Parameter\IntegerParameter;
use Chevere\Components\Parameter\Parameter;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Parameter\StringParameter;
use Chevere\Components\Regex\Regex;
use Chevere\Components\Type\Type;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseSuccessInterface;
use Chevereto\Components\Storage;

/**
 * Upload the filename to the target storage.
 */
class FileUploadAction extends Action
{
    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAddedRequired(
                new StringParameter('filename')
            )
            ->withAddedRequired(
                (new StringParameter('naming'))
                    ->withRegex(new Regex('/^(original|random|mixed|id)$/'))
                    ->withDefault('original')
            )
            ->withAddedRequired(
                (new StringParameter('originalName'))
                    ->withRegex(new Regex('/^.+\.[a-zA-Z]{3}$/'))
            )
            ->withAddedRequired(
                new Parameter('storage', new Type(Storage::class))
            )
            ->withAddedRequired(
                new StringParameter('uploadPath')
            );
    }

    public function getResponseDataParameters(): ParametersInterface
    {
        return (new Parameters);
    }

    public function run(array $arguments): ResponseSuccessInterface
    {
        $arguments = $this->getArguments($arguments);
        $filename = $arguments->getString('filename');
        $naming = $arguments->getString('naming');
        $originalName = $arguments->getString('originalName');
        /**
         * @var Storage $storage
         */
        $storage = $arguments->get('storage');

        return $this->getResponseSuccess(['path' => '123']);
    }
}
