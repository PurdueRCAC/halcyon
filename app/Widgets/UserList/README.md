## User List Widget

This displays a list users with a specified role or an individual user's profile if the last URL segment matches a user's username.

The list of users can be limited by user role and sorted by name, email, or creation date. Various profile data, such as title, bio, etc. can be enabled or disabled. Profile information is retrieved from user attributes (`App\Modules\Users\Models\Facet`).

### Dependencies

* `App\Modules\Users` (data source)
