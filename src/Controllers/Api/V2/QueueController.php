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

namespace Chevereto\Controllers\Api\V2;

use Chevere\Components\Action\Controller;
use Chevere\Components\Message\Message;
use Chevere\Components\Workflow\Step;
use Chevere\Components\Workflow\Workflow;
use Chevere\Exceptions\Core\OutOfBoundsException;
use Chevere\Exceptions\Service\ServiceException;
use Chevere\Interfaces\Workflow\TaskInterface;
use Chevere\Interfaces\Workflow\WorkflowInterface;
use Chevereto\Components\Settings;
use Throwable;

abstract class QueueController extends Controller
{
    protected Settings $settings;

    abstract public function getSettingsKeys(): array;

    /**
     * @return Array<string, TaskInterface>
     */
    abstract public function getSteps(): array;

    final public function getWorkflow(): WorkflowInterface
    {
        $class = get_class($this);
        $name = str_replace('\\', '-', $class);
        $workflow = new Workflow($name);
        foreach ($this->getSteps() as $k => $v) {
            $workflow = $workflow->withAdded(new Step($k), $v);
        }

        return $workflow;
    }

    /**
     * @throws OutOfBoundsException
     */
    final public function withSettings(Settings $settings): self
    {
        $settings->assertHasKey(...$this->getSettingsKeys());
        $new = clone $this;
        $new->settings = $settings;

        return $new;
    }

    /**
     * @throws ServiceException If called before `withSettings`.
     */
    final public function settings(): Settings
    {
        try {
            return $this->settings;
        } catch (Throwable $e) {
            throw new ServiceException(
                (new Message('Missing %service% service'))
                    ->code('%service%', Settings::class)
            );
        }
    }
}
