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

namespace Chevereto\Controllers\Api\V2\Account\Asset;

use Chevere\Components\Workflow\Step;
use Chevere\Components\Workflow\Workflow;
use Chevere\Interfaces\Workflow\WorkflowInterface;
use Chevereto\Actions\Account\Asset\AccountAssetAction;
use Chevereto\Actions\File\FileValidateAction;
use Chevereto\Actions\Image\ImageValidateMediaAction;

final class AccountAssetBackgroundPostBinaryController extends AccountAssetPostBinaryController
{
    public function getDescription(): string
    {
        return 'Uploads a binary image resource to be used as account background';
    }

    public function getBaseWorkflow(): WorkflowInterface
    {
        return new Workflow(
            asset: new Step(
                AccountAssetAction::class,
                format: '${accountBackgroundFormat}',
                path: '${accountBackgroundPath}'
            ),
            validateFile: new Step(
                FileValidateAction::class,
                mimes: '${mimes}',
                filepath: '${uploadFilepath}',
                maxBytes: '${accountBackgroundMaxBytes}',
                minBytes: '${accountBackgroundMinBytes}',
            ),
            validateMedia: new Step(
                ImageValidateMediaAction::class,
                filepath: '${uploadFilepath}',
                maxHeight: '${accountBackgroundMaxHeight}',
                maxWidth: '${accountBackgroundMaxWidth}',
                minHeight: '${accountBackgroundMinHeight}',
                minWidth: '${accountBackgroundMinWidth}',
            ),
        );
    }
}
