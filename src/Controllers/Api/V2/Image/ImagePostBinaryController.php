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

namespace Chevereto\Controllers\Api\V2\Image;

use Chevere\Components\Controller\Controller;
use Chevere\Components\Message\Message;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Parameter\StringParameter;
use Chevere\Components\Serialize\Unserialize;
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Service\ServiceableInterface;
use Chevereto\Controllers\Api\V2\Image\Traits\ImagePostTrait;
use Throwable;

final class ImagePostBinaryController extends Controller implements ServiceableInterface
{
    use ImagePostTrait;

    public function withSetUp(): self
    {
        $new = clone $this;
        $new->workflow = $this->getWorkflow();

        return $new;
    }

    public function getDescription(): string
    {
        return 'Uploads a binary image resource.';
    }

    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAddedRequired(
                (new StringParameter('source'))
                    ->withDescription('A binary image.')
            );
    }

    public function assertStoreSource($source, string $uploadFile): void
    {
        try {
            $unserialize = new Unserialize($source);
            $uploadFile = $unserialize->var()['tmp_name'];
        } catch (Throwable $e) {
            throw new InvalidArgumentException(
                new Message('Invalid binary image'),
                $e->getCode()
            );
        }
    }
}
