## Role Provision Listener

Service connector for Purdue University's Central Accounts, used for managing roles on users. Roles are ususally associated to a Resource and allow a user to connect or login to said resource.

### Events

This listens for the following events:

* `ResourceMemberCreated` - Set up a resource role (ex: "sholar") for a given user.
* `ResourceMemberStatus` - Check the status of a given role for a user. The service will respond with one of the given roles:
  * `NO_ROLE_EXISTS`
  * `ROLE_ACCOUNT_CREATION_PENDING`
  * `ROLE_ACCOUNTS_READY`
  * `ROLE_REMOVAL_PENDING`
* `ResourceMemberDeleted` - Remove a resource role for a given user.
