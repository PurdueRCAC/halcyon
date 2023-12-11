## Menus Module

Handle management of site menus.

### Registering Pages

When creating or editing a menu item, a `App\Modules\Menus\Events\CollectingRoutes` event is dispatched. Modules may listen for that event and append routes they manage to the list of available pages.

### Displaying Menus

The Widget module (`App\Modules\Widgets`) is required.

A widget is required to display a menu. The Menu widget should already be registered with the system. A new instance of the widget should be created and a `position` chosen.
