## Queues Module

Handle management of Resource queues.

**Note**: This is _not_ for the management of message queues. See the Messages module for that.

## Dependencies

* Users Module
* Groups Module
* Resources Module

## Command line options

Every command has an optional `--debug` flag that will run the command in a non-modification mode and output the built emails (but will **not** send the email).

`queues:emailexpired`

This will email expired accounts to the managers of the group that owns the queues.

`queues:emailfreeauthorized`

Email latest authorized group queue user entries to the managers of the group that owns the queues.

This is a scheduled task with a default crontab of `*/20 * * * *` (every 20 minutes).

`queues:emailfreedenied`

Email latest groupqueueuser denials.

This is a scheduled task with a default crontab of `*/20 * * * *` (every 20 minutes).

`queues:emailfreeremoved`

Email new groupqueueuser removals.

This is a scheduled task with a default crontab of `*/20 * * * *` (every 20 minutes).

`queues:emailfreerequested`

Email latest groupqueueuser requests.

This is a scheduled task with a default crontab of `*/20 * * * *` (every 20 minutes).

`queues:emailqueueauthorized`

Email authorized queue access requests.

This is a scheduled task with a default crontab of `*/10 * * * *` (every 10 minutes).

`queues:emailqueuedenied`

Email denied queue access requests.

This is a scheduled task with a default crontab of `*/20 * * * *` (every 20 minutes).

`queues:emailqueueremoved`

Email queue access removals.

This is a scheduled task with a default crontab of `*/20 * * * *` (every 20 minutes).

`queues:emailqueuerequested`

Email latest queue requests.

This is a scheduled task with a default crontab of `*/20 * * * *` (every 20 minutes).

`queues:emailwelcomecluster`

Email welcome message to new cluster users.

This is a scheduled task with a default crontab of `0 5 * * *` (every 5 hours).

`queues:emailwelcomefree`

Email welcome message to new free resource users.

This is a scheduled task with a default crontab of `0 5 * * *` (every 5 hours).

## Subscribed Events

 * `App\Modules\Users\Events\UserDeleted` - Remove membership to queues when a user account is removed.
 * `App\Modules\Users\Events\UserBeforeDisplay` - When a User's page is being displayed, this pulls associated Queues.
