## Home (Storage) Listener

This performs any necessary setup or other functions of home directories for a resource.

### Listens for

* `App\Modules\Resources\Events\ResourceMemberCreated` - Checks for a Home directory and creates if needed (this adds messages to message queue to create the directory ont he filesystem).
