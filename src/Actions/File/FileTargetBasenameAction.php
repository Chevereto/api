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
use Chevere\Components\Dependent\Dependencies;
use Chevere\Components\Dependent\Traits\DependentTrait;
use Chevere\Components\Parameter\IntegerParameter;
use Chevere\Components\Parameter\ObjectParameter;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Parameter\StringParameter;
use Chevere\Components\Regex\Regex;
use Chevere\Interfaces\Dependent\DependenciesInterface;
use Chevere\Interfaces\Dependent\DependentInterface;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Chevereto\Components\Db;
use Chevereto\Components\Storage\Storage;

/**
 * Determines the best (available) file target basename.
 *
 * Response parameters:
 *
 * ```php
 * basename: string,
 * ```
 */
class FileTargetBasenameAction extends Action implements DependentInterface
{
    use DependentTrait;

    private Db $db;

    public function getDependencies(): DependenciesInterface
    {
        return (new Dependencies())
            ->withPut(
                db: Db::class
            );
    }

    public function getParameters(): ParametersInterface
    {
    return (new Parameters())
        ->withAddedRequired(
            id: new IntegerParameter(),
            name: (new StringParameter())
                ->withRegex(new Regex('/^.+\.[a-zA-Z]+$/')),
            naming: (new StringParameter())
                ->withRegex(new Regex('/^(original|random|mixed|id)$/'))
                ->withDefault('original'),
            storage: new ObjectParameter(Storage::class)
        );
    }

    public function getResponseDataParameters(): ParametersInterface
    {
        return (new Parameters())
            ->withAddedRequired(
                basename: (new StringParameter())
                    ->withRegex(new Regex('/^.+\.[a-zA-Z]+$/'))
            );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $name = $arguments->getString('name');
        /** @var Storage $storage */
        $storage = $arguments->get('storage');
        while($storage->adapter()->fileExists($name)) {
            $name .= 'e';
        }

        return $this->getResponse(basename: $name);
    }
}