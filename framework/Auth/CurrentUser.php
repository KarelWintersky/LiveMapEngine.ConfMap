<?php

namespace LiveMapEngine\Auth;

use Arris\DelightAuth\Auth\Auth;
use Arris\Helpers\Server;

class CurrentUser implements \ArrayAccess
{
    /**
     * @var int|null
     */
    public ?int $id;

    /**
     * @var bool
     */
    public bool $is_logged_in;

    /**
     * @var string|null
     */
    public ?string $username;

    /**
     * @var string|null
     */
    public ?string $email;

    /**
     * @var string
     */
    public string $ipv4;

    /**
     * @var bool
     */
    public bool $is_admin;

    public function __construct(Auth $ac)
    {
        $this->id = $ac->id();
        $this->is_logged_in = $ac->isLoggedIn();
        $this->username = $ac->getUsername();
        $this->email = $ac->getEmail();
        $this->ipv4 = Server::getIP();

        $this->is_admin = $ac->hasRole(\LiveMapEngine\Auth\AuthRoles::ADMIN);
    }

    public function offsetExists(mixed $offset): bool
    {
        return property_exists($this, $offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->{$offset};
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->{$offset} = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        if ($this->offsetExists($offset)) {
            unset($this->{$offset});
        }
    }
}