<?php

namespace App\Listeners\Auth\Ldap\Events;

use LdapRecord\Models\Entry as LdapUser;

class AuthenticationFailed
{
    /**
     * The user that failed authentication.
     *
     * @var LdapUser
     */
    public $user;

    /**
     * Constructor.
     *
     * @param LdapUser $user
     */
    public function __construct(LdapUser $user)
    {
        $this->user = $user;
    }
}
