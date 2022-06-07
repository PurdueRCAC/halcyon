# Groups Module

Handle management of groups.

## Dependencies

* Users Module (associate a report to a user)
* Queues Module (associate a report to a group)
* Resources Module (tag relevant resources)

## Command line options

Every command has an optional `--debug` flag that will run the command in a non-modification mode and output the built emails (but will **not** send the email).

### Email latest group member authorizations

`groups:emailauthorized`

This will email the latest group member authorizations. It consists of two emails:

1. An email to the manager(s) of the group about the newly authorized user
2. An email to the newly authorized user

This is a scheduled task with a default crontab of `*/20 * * * *` (every 20 minutes).

### Email latest group member removals

`groups:emailremoved`

This will email the latest group membership removals. It consists of two emails:

1. An email to the manager(s) of the group about the removed user(s)
2. An email to the removed user

This is a scheduled task with a default crontab of `*/20 * * * *` (every 20 minutes).

## Subscribed Events

 * `App\Modules\Queues\Events\UserRequestUpdated` - Add membership to the base unix group of the Group the user made the request to.
 * `App\Modules\Users\Events\UserBeforeDisplay` - When a User's page is being displayed, this pulls associated Groups.

## Event Flows

Some event flows interact with 3rd-party services (via events triggers) and other modules (sometimes events, sometimes direct usage). These are documented below for easier reference.

### Enabling a queue

When enabling a queue for a group member via the GUI interface, it performs the following actions:

* Call `/api/queue/users`
* Validate incoming data
  * Check user exists
  * Check queue exists
  * Make sure actor has permissions to perform this action
  * Check if the user has access to the resource the queue is on
    * event: `App\Modules\Resources\Events\ResourceMemberStatus` - Check role status for the resource
      * If status is none or pending, create a resource member entry
        * event: `App\Modules\Resources\Events\ResourceMemberCreated`
* Create `queueusers` entry
  * event: `App\Modules\Queues\Events\QueueUserCreated`

### Enabling unix group membership

When enabling unix group membership for a group member via the GUI interface, it performs the following actions:

* Call `/api/unixgroups/members`
* Validate incoming data
  * Check user exists
  * Check unix group exists
  * Make sure actor has permissions to perform this action
* Create `unixgroupusers` entry
  * event: `App\Modules\Groups\Events\UnixGroupMemberCreated`
* Check if the unix group is the base unix group
  * If not, add the user to the base unix group as well
