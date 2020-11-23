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
use Chevereto\Components\Enqueue;
use Chevereto\Components\Settings;
use Throwable;

abstract class QueueController extends Controller implements ServiceableInterface
{
    use ServiceableTrait;

    protected Enqueue $enqueue;

    protected WorkflowInterface $workflow;

    protected Settings $settings;

    abstract public function getSettingsKeys(): array;

    /**
     * @return Array<string, TaskInterface>
     */
    abstract public function getTasks(): array;

    final public function withEnqueue(Enqueue $enqueue): self
    {
        $new = clone $this;
        $new->enqueue = $enqueue;

        return $new;
    }

    /**
     * @throws ServiceException If called before `withEnqueue`.
     */
    final public function enqueue(): Enqueue
    {
        try {
            return $this->enqueue;
        } catch (Throwable $e) {
            throw new ServiceException(
                $this->getMissingServiceMessage(Enqueue::class)
            );
        }
    }

    final public function withWorkflow(WorkflowInterface $workflow): self
    {
        $new = clone $this;
        $new->workflow = $workflow;

        return $new;
    }

    /**
     * @throws ServiceException If called before `withWorkflow`.
     */
    final public function workflow(): WorkflowInterface
    {
        try {
            return $this->workflow;
        } catch (Throwable $e) {
            throw new ServiceException(
                $this->getMissingServiceMessage(WorkflowInterface::class)
            );
        }
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
            ->withAdded('withEnqueue')
            ->withAdded('withWorkflow')
            ->withAdded('withSettings');
    }
}
