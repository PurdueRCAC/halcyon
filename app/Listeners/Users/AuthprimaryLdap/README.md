## AuthPrimary (LDAP) Listener

This connects to the RCAC AuthPrimary LDAP to retrieve user info.

### Listens for

* `App\Modules\Users\Events\UserSync` - Checks allPeople and People LDAP trees and based on if the user is authorized adds, modifies, or removes entries.

The following are used for checking, granting, and removing access manually to the resource.

* `App\Modules\Resources\Events\ResourceMemberCreated`
* `App\Modules\Resources\Events\ResourceMemberStatus`
* `App\Modules\Resources\Events\ResourceMemberDeleted`

The following are used for keeping authentication in sync with membership in unix groups.

* `App\Modules\Groups\Events\UnixGroupCreating`
* `App\Modules\Groups\Events\UnixGroupDeleted`
* `App\Modules\Groups\Events\UnixGroupMemberCreated`
* `App\Modules\Groups\Events\UnixGroupMemberDeleted`

#### Sample LDAP Entries

All People:

```
# example, AllPeople, anvil.rcac.purdue.edu
dn: uid=example,ou=AllPeople,dc=anvil,dc=rcac,dc=purdue,dc=edu
objectClass: posixAccount
objectClass: inetOrgPerson
objectClass: top
uid: example
uidNumber: 20972
gidNumber: 6751
homeDirectory: /home/example
loginShell: /bin/tcsh
cn: Ex A Mple
sn: Ex
```

(Authorized) People:

```
# example, People, anvil.rcac.purdue.edu
dn: uid=example,ou=People,dc=anvil,dc=rcac,dc=purdue,dc=edu
objectClass: posixAccount
objectClass: inetOrgPerson
objectClass: top
uid: example
uidNumber: 20972
gidNumber: 6751
homeDirectory: /home/example
loginShell: /bin/tcsh
cn: Ex A Mple
givenName: Ex A
sn: Mple
gecos: Ex A Mple
telephoneNumber: 49-61741
```

Group:

```
# x-peb216887, Groups, anvil.rcac.purdue.edu
dn: cn=x-peb216887,ou=Groups,dc=anvil,dc=rcac,dc=purdue,dc=edu
cn: x-peb216887
gidNumber: 7000167
objectClass: posixGroup
objectClass: top
memberUid: x-username1
memberUid: x-username2
```
