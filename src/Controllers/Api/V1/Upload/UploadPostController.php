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

namespace Chevereto\Controllers\Api\V1\Upload;

use Chevere\Components\Controller\Controller;
use Chevere\Components\Message\Message;
use Chevere\Components\Parameter\Parameter;
use Chevere\Components\Parameter\ParameterOptional;
use Chevere\Components\Parameter\Parameters;
use Chevere\Components\Plugin\PluggableAnchors;
use Chevere\Components\Plugin\Plugs\Hooks\Traits\PluggableHooksTrait;
use Chevere\Components\Regex\Regex;
use Chevere\Components\Response\ResponseSuccess;
use Chevere\Components\Str\StrBool;
use Chevere\Components\Workflow\Task;
use Chevere\Components\Workflow\Workflow;
use Chevere\Components\Workflow\WorkflowRun;
use Chevere\Exceptions\Core\InvalidArgumentException;
use Chevere\Exceptions\Core\RuntimeException;
use Chevere\Interfaces\Controller\ControllerInterface;
use Chevere\Interfaces\Parameter\ArgumentsInterface;
use Chevere\Interfaces\Parameter\ParametersInterface;
use Chevere\Interfaces\Plugin\PluggableAnchorsInterface;
use Chevere\Interfaces\Plugin\Plugs\Hooks\PluggableHooksInterface;
use Chevere\Interfaces\Response\ResponseInterface;
use Chevere\Interfaces\Workflow\WorkflowInterface;
use Chevereto\Actions\Image\UploadImage;
use Chevereto\Actions\Image\ValidateImage;
use Laminas\Uri\UriFactory;
use function Chevere\Components\Workflow\workflowRunner;

final class UploadPostController extends Controller implements PluggableHooksInterface
{
    use PluggableHooksTrait;

    private WorkflowInterface $workflow;

    public static function getHookAnchors(): PluggableAnchorsInterface
    {
        return (new PluggableAnchors)
            ->withAdded('setParameters')
            ->withAdded('setWorkflow');
    }

    public function getDescription(): string
    {
        return 'Uploads an image resource.';
    }

    public function getParameters(): ParametersInterface
    {
        return (new Parameters)
            ->withAdded(
                (new Parameter('source'))
                    ->withDescription('A base64 image string OR an image URL. It also takes image multipart/form-data.')
            )
            ->withAdded(
                (new Parameter('key'))
                    ->withDescription('API V1 key.')
            )
            ->withAdded(
                (new ParameterOptional('format', new Regex('/^(json|redirect|txt)$/')))
                    ->withDescription('Response document output format. Defaults to `json`.')
            );
    }

    public function setUp(): ControllerInterface
    {
        $new = clone $this;
        $new->workflow = $this->getWorkflow();
        $new->hook('setParameters', $new->parameters);
        $new->hook('setWorkflow', $new->workflow);

        return $new;
    }

    public function getWorkflow(): WorkflowInterface
    {
        return (new Workflow('api-v1-upload-get-controller'))
            ->withAdded(
                'validate',
                (new Task(ValidateImage::class))
                    ->withArguments(['filename' => '${filename}'])
            )
            ->withAdded(
                'upload',
                (new Task(UploadImage::class))
                    ->withArguments([
                        'filename' => '${filename}',
                        'userId' => '${userId}'
                    ])
            );
    }

    public function run(ArgumentsInterface $arguments): ResponseInterface
    {
        /**
         * @var string $source
         */
        // $raw_source = $arguments->get('source');
        // $unserialize = @unserialize($raw_source);
        // if ($unserialize === false) {
        //     $temp_file = tempnam(sys_get_temp_dir(), 'chv.temp');
        //     if ($temp_file === false || !is_writable($temp_file)) {
        //         throw new RuntimeException(
        //             new Message("Can't get a tempnam."),
        //             200
        //         );
        //     }
        //     $uri = UriFactory::factory($source);
        //     if ($uri->isValid()) {
        //         // Fetch $source to a $temp_file
        //         G\fetch_url($raw_source, $temp_file);
        //     } else {
        //         $source = trim(preg_replace('/\s+/', '', $raw_source));
        //         $double = base64_encode(base64_decode($source));
        //         if (!(new StrBool($source))->same($double)) {
        //             throw new InvalidArgumentException(
        //                 new Message('Invalid base64 string.'),
        //                 120
        //             );
        //         }
        //         unset($double);
        //         $fh = fopen($temp_file, 'w');
        //         stream_filter_append($fh, 'convert.base64-decode', STREAM_FILTER_WRITE);
        //         if (!@fwrite($fh, $source)) {
        //             throw new InvalidArgumentException(
        //                 new Message('Invalid base64 string.'),
        //                 130
        //             );
        //         }
        //         fclose($fh);
        //     }
        // } else {
        //     $temp_file = $source['tmp_name'];
        // }

        $temp_file = 'eee';

        $array = [
            'filename' => $temp_file,
            'userId' => null,
        ];
        $workflowRun = workflowRunner(new WorkflowRun($this->workflow, $array));

        return new ResponseSuccess([
            'id' => $workflowRun->get('upload')->data()['id'],
        ]);
    }
}
