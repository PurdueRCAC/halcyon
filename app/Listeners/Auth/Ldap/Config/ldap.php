<?php
return [
    'connection' => [
        /*
        |--------------------------------------------------------------------------
        | Schema
        |--------------------------------------------------------------------------
        |
        | The schema class to use for retrieving attributes and generating models.
        | You can also set this option to `null` to use the default schema class.
        |
        | For Active Directory, you must use the schema:
        |
        |    Adldap\Schemas\ActiveDirectory::class
        |
        | For OpenLDAP, you must use the schema:
        |
        |   Adldap\Schemas\OpenLDAP::class
        |
        | For FreeIPA, you must use the schema:
        |
        |   Adldap\Schemas\FreeIPA::class
        |
        | Custom schema classes must implement Adldap\Schemas\SchemaInterface
        |
        */
        'schema' => Adldap\Schemas\ActiveDirectory::class,

        /*
        |--------------------------------------------------------------------------
        | Domain Controllers
        |--------------------------------------------------------------------------
        |
        | The domain controllers option is an array of servers located on your
        | network that serve Active Directory. You can insert as many servers or
        | as little as you'd like depending on your forest (with the
        | minimum of one of course).
        |
        | These can be IP addresses of your server(s), or the host name.
        |
        */
        'hosts' => explode(' ', env('LDAP_HOST', 'corp-dc1.corp.acme.org corp-dc2.corp.acme.org')),

        /*
        |--------------------------------------------------------------------------
        | Port
        |--------------------------------------------------------------------------
        |
        | The port option is used for authenticating and binding to your LDAP server.
        |
        */
        'port' => env('LDAP_PORT', 389),

        /*
        |--------------------------------------------------------------------------
        | SSL & TLS
        |--------------------------------------------------------------------------
        |
        | If you need to be able to change user passwords on your server, then an
        | SSL or TLS connection is required. All other operations are allowed
        | on unsecured protocols.
        |
        | One of these options are definitely recommended if you
        | have the ability to connect to your server securely.
        |
        */
        'use_ssl' => env('LDAP_USE_SSL', false),
        'use_tls' => env('LDAP_USE_TLS', false),

        /*
        |--------------------------------------------------------------------------
        | Base Distinguished Name
        |--------------------------------------------------------------------------
        |
        | The base distinguished name is the base distinguished name you'd
        | like to perform query operations on. An example base DN would be:
        |
        |        dc=corp,dc=acme,dc=org
        |
        | A correct base DN is required for any query results to be returned.
        |
        */
        'base_dn' => env('LDAP_BASEDN', 'dc=corp,dc=acme,dc=org'),

        /*
        |--------------------------------------------------------------------------
        | LDAP Username & Password
        |--------------------------------------------------------------------------
        |
        | When connecting to your LDAP server, a username and password is required
        | to be able to query and run operations on your server(s). You can
        | use any user account that has these permissions. This account
        | does not need to be a domain administrator unless you
        | require changing and resetting user passwords.
        |
        */
        'username' => env('LDAP_USERNAME', 'username'),
        'password' => env('LDAP_PASSWORD', 'password'),
    ],

    /*
    |--------------------------------------------------------------------------
    | LDAP
    |--------------------------------------------------------------------------
    |
    | Locate Users By:
    |
    |   This value is the users attribute you would like to locate LDAP
    |   users by in your directory.
    |
    |   For example, using the default configuration below, if you're
    |   authenticating users with an email address, your LDAP server
    |   will be queried for a user with the a `userprincipalname`
    |   equal to the entered email address.
    |
    | Bind Users By:
    |
    |   This value is the users attribute you would
    |   like to use to bind to your LDAP server.
    |
    |   For example, when a user is located by the above attribute,
    |   the users attribute you specify below will be used as
    |   the 'username' to bind to your LDAP server.
    |
    |   This is usually their distinguished name.
    |
    */
    'locate_users_by' => 'userprincipalname',
    'bind_users_by' => 'distinguishedname',

    /*
    |--------------------------------------------------------------------------
    | Sync Attributes
    |--------------------------------------------------------------------------
    |
    | Attributes specified here will be added / replaced on the user model
    | upon login, automatically synchronizing and keeping the attributes
    | up to date.
    |
    | The array key represents the users Laravel model key, and
    | the value represents the user's LDAP attribute.
    |
    | You **must** include the users login attribute here.
    |
    */
    'sync_attributes' => [

        'email' => 'userprincipalname',
        'name' => 'cn',

    ],
];
