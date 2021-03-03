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
use DateTimeZone;

/**
 * @Entity
 * @Table(name="user")
 */
final class User
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    private int $id;

    /**
     * @Column(type="datetime", nullable=false)
     */
    private DateTimeImmutable $datetime_utc;

    /**
     * @Column(type="string", length=255, nullable=true)
     */
    private ?string $name;

    /**
     * @Column(type="string", length=255, nullable=false)
     */
    private string $username;

    /**
     * @Column(type="string", length=255, nullable=false)
     */
    private string $email;

    /**
     * @Column(type="string", length=255, nullable=true)
     */
    private ?string $website;

    /**
     * @Column(type="text", length=255, nullable=true)
     */
    private ?string $bio;

    /**
     * @Column(type="integer", nullable=false)
     */
    private int $timezone;

    /**
     * @Column(type="integer", nullable=false)
     */
    private int $language;

    /**
     * @Column(type="integer", nullable=false)
     */
    private int $status;

    /**
     * @Column(type="integer", nullable=false, options={"default": 0})
     */
    private int $public_images;

    /**
     * @Column(type="integer", nullable=false, options={"default": 0})
     */
    private int $public_videos;

    /**
     * @Column(type="integer", nullable=false, options={"default": 0})
     */
    private int $public_audios;

    /**
     * @Column(type="integer", nullable=false, options={"default": 0})
     */
    private int $followers;

    /**
     * @Column(type="integer", nullable=false, options={"default": 0})
     */
    private int $following;

    /**
     * @Column(type="integer", nullable=false, options={"default": 0})
     */
    private int $likes_given;

    /**
     * @Column(type="integer", nullable=false, options={"default": 0})
     */
    private int $likes_made;

    public function __construct(string ...$properties)
    {
        $this->datetime_utc = new DateTimeImmutable($properties['datetime_utc'], new DateTimeZone('UTC'));
        $stringProps = ['name', 'username', 'email', 'website', 'bio'];
        $integerProps = ['id', 'timezone', 'language', 'status', 'public_images', 'public_videos', 'public_audios', 'followers', 'following', 'likes_given', 'likes_made'];
        foreach ($stringProps as $stringProp) {
            $this->{$stringProp} = $properties[$stringProp];
        }
        foreach ($integerProps as $integerProp) {
            $this->{$integerProp} = (int) $properties[$integerProp];
        }
    }

    public function id(): int
    {
        return $this->id;
    }

    public function datetime_utc(): DateTimeImmutable
    {
        return $this->datetime_utc;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function username(): string
    {
        return $this->username;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function website(): string
    {
        return $this->website;
    }

    public function bio(): string
    {
        return $this->bio;
    }

    public function timezone(): int
    {
        return $this->timezone;
    }

    public function language(): int
    {
        return $this->language;
    }

    public function status(): int
    {
        return $this->status;
    }

    public function public_images(): int
    {
        return $this->public_images;
    }

    public function public_videos(): int
    {
        return $this->public_videos;
    }

    public function public_audios(): int
    {
        return $this->public_audios;
    }

    public function followers(): int
    {
        return $this->followers;
    }

    public function following(): int
    {
        return $this->following;
    }

    public function likes_given(): int
    {
        return $this->likes_given;
    }

    public function likes_made(): int
    {
        return $this->likes_made;
    }
}
