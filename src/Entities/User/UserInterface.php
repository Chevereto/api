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

namespace Chevereto\Entities\User;

use DateTimeImmutable;

/**
 * Describes the component in charge of defining the User entity interface.
 */
interface UserInterface
{
    public function __construct(string ...$properties);

    public function id(): int;

    public function datetime_utc(): DateTimeImmutable;

    public function name(): string;

    public function username(): string;

    public function email(): string;

    public function website(): string;

    public function bio(): string;

    public function timezone(): int;

    public function language(): int;

    public function status(): int;

    public function public_images(): int;

    public function public_videos(): int;

    public function public_audios(): int;

    public function followers(): int;

    public function following(): int;

    public function likes_given(): int;

    public function likes_made(): int;
}
