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
use Chevere\Components\Filesystem\Filename;
use Chevere\Components\Parameter\IntegerParameter;
use Chevere\Components\Parameter\ObjectParameter;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Parameter\StringParameter;
use Chevere\Components\Regex\Regex;
use function Chevere\Components\Str\randomString;
use Chevere\Interfaces\Filesystem\FilenameInterface;
use Chevere\Interfaces\Filesystem\PathInterface;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Chevereto\Components\Storage\Storage;

/**
 * Determines the best available target filename for the given storage, path and naming.
 */
class FileNamingAction extends Action
{
    public function getParameters(): ParametersInterface
    {
        return new Parameters(
            id: new IntegerParameter(),
            name: (new StringParameter())
                ->withRegex(new Regex('/^.+\.[a-zA-Z]+$/')),
            naming: (new StringParameter())
                ->withRegex(new Regex('/^original|random|mixed|id$/'))
                ->withDefault('original'),
            storage: new ObjectParameter(Storage::class),
            path: new ObjectParameter(PathInterface::class)
        );
    }

    public function getResponseParameters(): ParametersInterface
    {
        return new Parameters(
            filename: new ObjectParameter(Filename::class),
        );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $id = $arguments->getInteger('id');
        $encodedId = 'encoded';
        $name = $arguments->getString('name');
        $naming = $arguments->getString('naming');
        $file = new Filename($name);
        if ($naming === 'id') {
            return $this->getResponse(
                filename: new Filename($encodedId . '.' . $file->extension())
            );
        }
        /** @var Storage $storage */
        $storage = $arguments->get('storage');
        /** @var PathInterface $path */
        $path = $arguments->get('path');
        $name = $this->getName($naming, $file);
        while ($storage->adapter()->fileExists($path->getChild($name)->toString())) {
            if ($naming === 'original') {
                $naming = 'mixed';
            }
            $name = $this->getName($naming, $file);
        }

        return $this->getResponse(filename: new Filename($name));
    }

    public function getName(string $naming, FilenameInterface $filename): string
    {
        return match($naming) {
            'original' => $filename->toString(),
            'random' => $this->getRandomName($filename),
            'mixed' => $this->getMixedName($filename),
        };
    }

    private function getRandomName(FilenameInterface $filename): string
    {
        return randomString(32) . '.' . $filename->extension();
    }

    private function getMixedName(FilenameInterface $filename): string
    {
        $charsLength = 16;
        $chars = randomString($charsLength);
        $name = $filename->name();
        $nameLength = mb_strlen($name);
        $withExtensionLength = mb_strlen($filename->extension()) + 1;
        if ($nameLength + $charsLength > Filename::MAX_LENGTH_BYTES) {
            $chop = Filename::MAX_LENGTH_BYTES - $charsLength - $nameLength - $withExtensionLength;
            $name = mb_substr($name, 0, $chop);
        }

        return $name . $chars . '.' . $filename->extension();
    }
}
