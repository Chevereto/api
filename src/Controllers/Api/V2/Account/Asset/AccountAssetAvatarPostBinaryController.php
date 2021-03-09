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

final class AccountAssetAvatarPostBinaryController extends AccountAssetPostBinaryController
{
    public function getDescription(): string
    {
        return 'Uploads a binary image resource to be used as account avatar';
    }

    public function getBaseWorkflow(): WorkflowInterface
    {
        return new Workflow(
            asset: new Step(
                AccountAssetAction::class,
                format: '${accountAvatarFormat}',
                path: '${accountAvatarPath}'
            ),
            validateFile: new Step(
                FileValidateAction::class,
                mimes: '${accountAvatarMimes}',
                filepath: '${accountAvatarFilename}',
                maxBytes: '${accountAvatarMaxBytes}',
                minBytes: '${accountAvatarMinBytes}',
            ),
            validateMedia: new Step(
                ImageValidateMediaAction::class,
                filepath: '${uploadFilepath}',
                maxHeight: '${accountAvatarMaxHeight}',
                maxWidth: '${accountAvatarMaxWidth}',
                minHeight: '${accountAvatarMinHeight}',
                minWidth: '${accountAvatarMinWidth}',
            )
        );
    }
}
