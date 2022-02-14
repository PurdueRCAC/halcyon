## Rancher Listener

Service connector for Rancher, used for creating projects, setting resource limits, and adding/removing users.

### Overview

This makes calls to Rancher's web service to create projects, set resource limits, and add/remove users.

### Events

This listens for the following events:

* `QueueCreated` - Create a Rancher project representing the research group the queue is for
* `QueueUserCreated` - Add a user to a Rancher project
* `QueueUserDeleted` - Remove a user from a Rancher project
* `QueueSizeCreated`, `QueueSizeUpdated`, `QueueSizeDeleted`, `QueueLoanCreated`, `QueueLoanUpdated`, `QueueLoanDeleted` - Update project limits based on resources sold/loaned to the queue

### Troubleshooting

All calls are logged in the website's database. These can be directly examined with the following query (newest calls first):

```
SELECT * FROM log WHERE app='rancher' ORDER BY id DESC;
```

Additions and removals are also logged to PHP's error log at:

```
/var/log/httpd/error_log
```
