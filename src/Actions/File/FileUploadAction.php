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
use Chevere\Components\Parameter\ObjectParameter;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Parameter\StringParameter;
use Chevere\Interfaces\Filesystem\PathInterface;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Chevereto\Components\Storage\Storage;

/**
 * Upload the filename to the target storage.
 */
class FileUploadAction extends Action
{
    public function getParameters(): ParametersInterface
    {
        return new Parameters(
            filename: new StringParameter(),
            targetBasename: new ObjectParameter(Basename::class),
            storage: new ObjectParameter(Storage::class),
            path: new ObjectParameter(PathInterface::class)
        );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        $filename = $arguments->getString('filename');
        /** @var Basename $basename */
        $basename = $arguments->get('targetBasename');
        /** @var Storage $storage */
        $storage = $arguments->get('storage');
        // $storage->adapter()->write();

        return $this->getResponse();
    }
}
