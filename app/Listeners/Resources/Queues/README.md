## Queues Listener

This performs various actions on Queues when an action is performed on a related resource.

### Listens for

* `App\Modules\Resources\Events\SubresourceCreated` - Sets up a default Queue when a Subresource is created.
* `App\Modules\Resources\Events\AssetCreated` - Create a default scheduler for a new compute asset.
