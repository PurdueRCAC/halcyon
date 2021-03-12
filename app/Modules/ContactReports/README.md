# Contact Reports Module

Manage interactions (contact reports) with clients and potential clients.

## Dependencies

* Users Module (associate a report to a user)
* Groups Module (associate a report to a group)
* Resources Module (tag relevant resources)

## Command line options

Every command has an optional `--debug` flag that will run the command in a non-modification mode and output the built emails (but will **not** send the email).

### Email New Reports

`crm:emailreports`

This will email the latest Contact Reports to subscribers.

This is a scheduled task with a default crontab of `*/10 * * * *` (every 10 minutes).

### Email New Comments

`crm:emailcomments`

This will email the latest Contact Report comments to subscribers.

This is a scheduled task with a default crontab of `*/10 * * * *` (every 10 minutes).

### Email Followups

`crm:emailfollowups`

This will send an email to a user listed on a report, based on report type configuration. Each report type can specify a time period (days, weeks, moneths) after the report contact date to send a followup email.

This is a scheduled task with a default crontab of `0 10 * * 1-5` (weekdays at 10am).

## Subscribed Events

 * `App\Modules\Courses\Events\AccountCreated` - When a new course account is created, this listens for any course with a `report` attribute and creates a new Contact Report with the value of that attribute.
 * `App\Modules\Groups\Events\GroupReading` - When a Group is being read, this pulls associated Contact Reports.
