<?php

namespace App\Listeners\Auth\Ldap\Events;

use LdapRecord\Models\Entry as LdapUser;

class Authenticating
{
    /**
     * The LDAP user that is authenticating.
     *
     * @var LdapUser
     */
    public $user;

    /**
     * The username being used for authentication.
     *
     * @var string
     */
    public $username = '';

    /**
     * Constructor.
     *
     * @param LdapUser   $user
     * @param string $username
     */
    public function __construct(LdapUser $user, $username = '')
    {
        $this->user = $user;
        $this->username = $username;
    }
}
