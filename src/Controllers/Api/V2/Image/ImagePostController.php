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
use Chevere\Interfaces\Service\ServiceableInterface;
use Chevere\Interfaces\Workflow\WorkflowInterface;
use Chevereto\Controllers\Api\V2\Image\Traits\ImagePostTrait;

abstract class ImagePostController extends Controller implements ServiceableInterface
{
    use ImagePostTrait;

    /**
     * @codeCoverageIgnore
     */
    public function withWorkflow(WorkflowInterface $workflow): self
    {
        $new = clone $this;
        $new->workflow = $workflow;

        return $new;
    }
}
