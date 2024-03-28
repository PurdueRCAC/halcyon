<?php

namespace App\Listeners\Auth\Ldap\Events;

use LdapRecord\Models\Entry as LdapUser;

class DiscoveredWithCredentials
{
    /**
     * The discovered LDAP user before authentication.
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
