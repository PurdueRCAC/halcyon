## Depot Listener

Handle creation/removal of special directories for Data Depot.

### Listens for

* `App\Modules\Storage\Events\DirectoryCreated` - When a directory is created for the Depot resource, this:
  * Adds the necessary messages to the message queue to create the filesystem directory
  * If a top-level directory with bytes (i.e., space allocation):
    * create a directory entry for Fortress with the `{base unixgroup}-data` unix group applied (TODO: Maybe break this part out to a Fortress listener?)
    * create a directory entry for Box with `{base unixgroup}` unix group applied (TODO: Maybe break this part out to a Box listener?)
* `App\Modules\Storage\Events\DirectoryUpdated` - Adds the necessary messages to the message queue to update the filesystem directory
* `App\Modules\Storage\Events\DirectoryDeleted` - Adds the necessary messages to the message queue to delete the directory on the filesystem
* `App\Modules\Storage\Events\LoanCreated` - Update the bytes value on the top-level directory
* `App\Modules\Storage\Events\PurchaseCreated` - Update the bytes value on the top-level directory
