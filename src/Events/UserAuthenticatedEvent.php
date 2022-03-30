<?php

namespace App\Events;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * The user.authenticated event is dispatched each time a user is authenticated 
 * in the system.
 */
class UserAuthenticatedEvent extends Event
{
    public const NAME = 'user.authenticated';

    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
