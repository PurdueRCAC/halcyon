## Group Provision Listener

Service connector for Purdue University's Central Accounts, used for managing user membership in unix groups.

### Overview

This makes calls to central account's web service to add to, remove from, or check status of membership in a unix group. When a user is added to a unix group, a transd packet will be sent to xenon.rcac. From there, xenon adds the relevant unix group to the user. In the example calls below, "rcs12340" corresponds to a value in the "shortname" column of the unixgroups table. When a unix group is added and has propagated through all the systems, it should show up (on a resource):

```
$ groups janedoe
janedoe : example example-data example-apps otherexample 
```

### Listens for

* `App\Modules\Users\Events\UserUpdated`
* `App\Modules\Groups\Events\UnixGroupCreating`
* `App\Modules\Groups\Events\UnixGroupDeleted`
* `App\Modules\Groups\Events\UnixGroupMemberCreated`
* `App\Modules\Groups\Events\UnixGroupMemberDeleted`

### Troubleshooting

All calls are logged in the website's database. These can be directly examined with the following query (newest calls first):

```
SELECT * FROM log WHERE app='groupprovision' ORDER BY id DESC;
```

Additions and removals are also logged to PHP's error log at:

```
/var/log/httpd/error_log
```
