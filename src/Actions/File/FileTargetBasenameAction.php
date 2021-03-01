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
use Chevere\Components\Filesystem\Basename;
use Chevere\Components\Filesystem\Path;
use Chevere\Components\Parameter\ObjectParameter;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Parameter\StringParameter;
use Chevere\Components\Regex\Regex;
use function Chevere\Components\Str\randomString;
use Chevere\Interfaces\Filesystem\PathInterface;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Chevereto\Components\Storage\Storage;

/**
 * Determines the best available target basename for the given storage and path.
 *
 * Arguments:
 *
 * ```php
 * id: string,
 * name: string,
 * naming: string,
 * storage: Storage,
 * path: string,
 * ```
 *
 * Response:
 *
 * ```php
 * basename: Basename,
 * ```
 */
class FileTargetBasenameAction extends Action
{
    public function getParameters(): ParametersInterface
    {
        return new Parameters(
            id: new StringParameter(),
            name: (new StringParameter())
                ->withRegex(new Regex('/^.+\.[a-zA-Z]+$/')),
            naming: (new StringParameter())
                ->withRegex(new Regex('/^original|random|mixed|id$/'))
                ->withDefault('original'),
            storage: new ObjectParameter(Storage::class),
            path: new ObjectParameter(Path::class)
        );
    }

    public function getResponseDataParameters(): ParametersInterface
    {
        return new Parameters(
            basename: new ObjectParameter(Basename::class)
        );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $id = $arguments->getString('id');
        $name = $arguments->getString('name');
        $naming = $arguments->getString('naming');
        $basename = new Basename($name);
        if ($naming === 'id') {
            return $this->getResponse(
                basename: new Basename($id . '.' . $basename->extension())
            );
        }
        /** @var Storage $storage */
        $storage = $arguments->get('storage');
        /** @var PathInterface $path */
        $path = $arguments->get('path');
        $name = $this->getName($naming, $basename);
        while ($storage->adapter()->fileExists($path->getChild($name)->toString())) {
            if ($naming === 'original') {
                $naming = 'mixed';
            }
            $name = $this->getName($naming, $basename);
        }

        return $this->getResponse(basename: new Basename($name));
    }

    public function getName(string $naming, Basename $basename): string
    {
        return match($naming) {
            'original' => $basename->toString(),
            'random' => $this->getRandomName($basename),
            'mixed' => $this->getMixedName($basename),
        };
    }

    private function getRandomName(Basename $basename): string
    {
        return randomString(32) . '.' . $basename->extension();
    }

    private function getMixedName(Basename $basename): string
    {
        $charsLength = 16;
        $chars = randomString($charsLength);
        $name = $basename->name();
        $nameLength = mb_strlen($name);
        $withExtensionLength = mb_strlen($basename->extension()) + 1;
        if ($nameLength + $charsLength > Basename::MAX_LENGTH_BYTES) {
            $chop = Basename::MAX_LENGTH_BYTES - $charsLength - $nameLength - $withExtensionLength;
            $name = mb_substr($name, 0, $chop);
        }

        return $name . $chars . '.' . $basename->extension();
    }
}
