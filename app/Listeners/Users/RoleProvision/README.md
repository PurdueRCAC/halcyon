## Role Provision Listener

Service connector for Purdue University's Central Accounts, used for managing roles on users. Roles are ususally associated to a Resource and allow a user to connect or login to said resource.

### Overview

This makes calls to Purdue central account's web service to add, remove, or check status of a role. Roles correspond to a resource, such as "bell", "brown", etc. When a role is added, a transd packet will be sent to xenon.rcac. From there, xenon adds the relevant host (e.g., "bell.rcac.purdue.edu") to the user's LDAP entry. This is what allows the user to connect to the associated resource. You can find the applied roles as "host" when looking up a user in the RCAC LDAP.

```
$ ldapsearch -x uid=janedoe

# janedoe, People, rcac.purdue.edu
dn: uid=du153,ou=People,dc=rcac,dc=purdue,dc=edu
acctStatus: e
authorizedBy: coolguy69
cn: Jane Doe
gecos: Jane Doe
gidNumber: 132
givenName: Jane
homeDirectory: /home/janedoe
loginShell: /bin/bash
mailHost: janedoe@purdue.edu
objectClass: account
objectClass: acmaintAccount
objectClass: posixAccount
objectClass: sambaSamAccount
objectClass: shadowAccount
objectClass: top
sn: Doe
uid: janedoe
uidNumber: 123456
host: data.rcac.purdue.edu
host: scholar.rcac.purdue.edu
```

### Events

This listens for the following events:

* `ResourceMemberCreated` - Set up a resource role (ex: "scholar") for a given user.
* `ResourceMemberStatus` - Check the status of a given role for a user. The service will respond with one of the given roles:
  * `NO_ROLE_EXISTS`
  * `ROLE_ACCOUNT_CREATION_PENDING`
  * `ROLE_ACCOUNTS_READY`
  * `ROLE_REMOVAL_PENDING`
* `ResourceMemberDeleted` - Remove a resource role for a given user.

### Troubleshooting

All calls are logged in the website's database. These can be directly examined with the following query (newest calls first):

```
SELECT * FROM log WHERE app='roleprovision' ORDER BY id DESC;
```

Additions and removals are also logged to PHP's error log at:

```
/var/log/httpd/error_log
```
