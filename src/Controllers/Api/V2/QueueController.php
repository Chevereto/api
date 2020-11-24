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

use Chevere\Components\Controller\Controller;
use Chevere\Components\Message\Message;
use Chevere\Components\Service\ServiceProviders;
use Chevere\Components\Service\Traits\ServiceableTrait;
use Chevere\Components\Workflow\Workflow;
use Chevere\Components\Workflow\WorkflowMessage;
use Chevere\Components\Workflow\WorkflowRun;
use Chevere\Exceptions\Core\ArgumentCountException;
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Exceptions\Core\LogicException;
use Chevere\Exceptions\Core\OutOfBoundsException;
use Chevere\Exceptions\Core\OverflowException;
use Chevere\Exceptions\Core\UnexpectedValueException;
use Chevere\Exceptions\Service\ServiceException;
use Chevere\Interfaces\Service\ServiceableInterface;
use Chevere\Interfaces\Service\ServiceProvidersInterface;
use Chevere\Interfaces\Workflow\TaskInterface;
use Chevere\Interfaces\Workflow\WorkflowInterface;
use Chevere\Interfaces\Workflow\WorkflowMessageInterface;
use Chevereto\Components\Enqueue;
use Chevereto\Components\Settings;
use Throwable;

abstract class QueueController extends Controller implements ServiceableInterface
{
    use ServiceableTrait;

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
            $workflow = $workflow->withAdded($k, $v);
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

    /**
     * @throws InvalidArgumentException
     * @throws LogicException
     * @throws ArgumentCountException
     * @throws UnexpectedValueException
     * @throws OverflowException
     */
    final public function getServiceProviders(): ServiceProvidersInterface
    {
        return (new ServiceProviders($this))
            ->withAdded('withSettings');
    }
}
