## Fortress (storage) Listener

This performs any necessary setup or other functions of Fortress space for a resource.

### Listens for

* `App\Modules\Resources\Events\ResourceMemberCreated` - When someone is given access to a resource, this also gives access to Fortress.
* `App\Modules\Groups\Events\UnixGroupMemberCreated` - When someone is added to a unix group, this checks for and grants access to Fortress.
