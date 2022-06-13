<?php

namespace App\Listeners\Auth\Ldap\Events;

use Adldap\Models\User;

class AuthenticatedWithCredentials
{
    /**
     * The authenticated LDAP user.
     *
     * @var User
     */
    public $user;

    /**
     * Constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
