<?php

namespace App\Listeners\Auth\Ldap\Events;

use Adldap\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuthenticatedModelTrashed
{
    /**
     * The user that has been denied authentication.
     *
     * @var User
     */
    public $user;

    /**
     * The LDAP users eloquent model.
     *
     * @var Model|null
     */
    public $model;

    /**
     * Constructor.
     *
     * @param User       $user
     * @param Model|null $model
     */
    public function __construct(User $user, Model $model = null)
    {
        $this->user = $user;
        $this->model = $model;
    }
}
