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

namespace Chevereto\Controllers\Api\V2\File;

use Chevere\Components\Controller\Controller;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Service\ServiceProviders;
use Chevere\Components\Workflow\Task;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Parameter\StringParameterInterface;
use Chevere\Interfaces\Service\ServiceableInterface;
use Chevere\Interfaces\Service\ServiceProvidersInterface;
use Chevere\Interfaces\Workflow\TaskInterface;
use Chevere\Interfaces\Workflow\WorkflowInterface;
use Chevereto\Actions\File\DetectDuplicateAction;
use Chevereto\Actions\File\ValidateAction;
use Chevereto\Actions\Storage\FailoverAction;
use Chevereto\Components\Settings;

abstract class FilePostController extends Controller implements ServiceableInterface
{
    private Settings $settings;

    private WorkflowInterface $workflow;

    abstract public function getSettingsKeys(): array;

    abstract public function assertStoreSource(string $source, string $uploadFile): void;

    abstract public function getSourceParameter(): StringParameterInterface;

    abstract public function getValidateTask(): TaskInterface;

    abstract public function getWorkflow(): WorkflowInterface;

    public function getParameters(): ParametersInterface
    {
        $source = $this->getSourceParameter();

        return (new Parameters)
            ->withAddedRequired($source);
    }

    public function getValidateFileTask(): TaskInterface
    {
        return (new Task(ValidateAction::class))
            ->withArguments([
                'extensions' => '${extensions}',
                'filename' => '${filename}',
                'maxBytes' => '${maxBytes}',
                'minBytes' => '${minBytes}',
            ]);
    }

    public function getDetectDuplicateTask(): TaskInterface
    {
        return (new Task(DetectDuplicateAction::class))
            ->withArguments([
                'md5' => '${validate:md5}',
                'perceptual' => '${validate:perceptual}',
                'ipv4' => '${ipv4}',
                'ipv6' => '${ipv6}',
            ]);
    }

    public function getStorageFailoverTask(): TaskInterface
    {
        return (new Task(FailoverAction::class))
            ->withArguments([
                'storageId' => 0
            ]);
    }

    /**
     * @codeCoverageIgnore
     */
    public function withWorkflow(WorkflowInterface $workflow): self
    {
        $new = clone $this;
        $new->workflow = $workflow;

        return $new;
    }

    public function getServiceProviders(): ServiceProvidersInterface
    {
        return (new ServiceProviders($this))
            ->withAdded('withSettings');
    }

    /**
     * @throws OutOfBoundsException
     */
    public function withSettings(Settings $settings): self
    {
        $settings->assertHasKey(...$this->getSettingsKeys());
        $new = clone $this;
        $new->settings = $settings;

        return $new;
    }

    public function settings(): Settings
    {
        return $this->settings;
    }
}
