<?php

namespace App\Listeners\Auth\Ldap\Events;

use LdapRecord\Models\Entry as LdapUser;
use Illuminate\Database\Eloquent\Model;

class AuthenticationSuccessful
{
    /**
     * The LDAP user that has successfully authenticated.
     *
     * @var LdapUser
     */
    public $user;

    /**
     * The authenticated LDAP users model.
     *
     * @var Model|null
     */
    public $model;

    /**
     * Constructor.
     *
     * @param LdapUser   $user
     * @param Model|null $model
     */
    public function __construct(LdapUser $user, Model $model = null)
    {
        $this->user = $user;
        $this->model = $model;
    }
}
