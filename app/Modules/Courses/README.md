## Courses Module

This module allows for users to select a class or workshop they are an instructor off and create an associated Course Account. Enrollments from the selected class or workshop are then given the appropriate resource role, allowing login access to the resource.

### Command line options

Every command has an optional `--debug` flag that will run the command in a non-modification mode and only report changes it _would_ make.

#### Sync

`artisan course:sync`

This command has a `--log` option for logging informational/debug statements to PHP's error log.

The Sync command retrieves enrollment information for registered course accounts, determines who should have the appropriate resource role (`{resource}.rcac.purdue.edu`) and adds or removes the role as needed. Application of a role is determined by the start and end dates for a course account. The execution thread is as follows:

* Select all (non-trashed) course accounts with an end time > now
* Look up class info for each instructor of an account
  * This triggers an `App\Modules\Courses\EventsAccountInstructorLookup` event for each instructor
    * `App\Listeners\Courses\UniTime` listens for this event and retrieves information from Purdue's Timetable service
  * Each account that finds an association instructor class is pushed to a new list
* Loop though the list of matched accounts and look up enrollment information
  * This triggers an `App\Modules\Courses\Events\AccountEnrollment` event for each account
    * `App\Listeners\Courses\UniTime` listens for this event and retrieves enrollment information
* For each enrolled student:
  * Look up a local account
    * Failing that, trigger a `App\Modules\Users\Events\UserLookup` event
      * `App\Listeners\Users\DbmLdap` listens for this event and retrieves information from the Purdue LDAP
        * Failing that, skip user
  * Check for a course user entry
    * Create one if not found
* Add list of explicitely declared users (course user table) to student list
* Trigger `App\Modules\Courses\Events\CourseEnrollment` event with final student list
  * `App\Listeners\Users\RcacLdap` listens for this event, looks up all users with the associated resource role, and compares against the passed-in student list. This generates two lists of users that need to be added (in the pass-in list but do not have a role) and users that should be removed (not in the passed-in list but do have a role).
* Add users from the 'create users' list
  * Triggers `App\Modules\Resources\Events\ResourceMemberStatus` event
  * Triggers `App\Modules\Resources\Events\ResourceMemberCreated` event
* Remove users from the 'remove users' list
  * Triggers `App\Modules\Resources\Events\ResourceMemberDeleted` event

#### Email Additions

`artisan course:emailadditions`

This command looks for any new course users with `notice=1` and sends an email to the associated account's instructor, notifying them of the additions.

#### Email Removals

`artisan course:emailremovals`

This command looks for any course user with `notice=2` and sends an email to the associated account's instructor, notifying them of the removals.
