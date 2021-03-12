## History Module

This module allows for browsing and inspecting site activity and the history of changes to data. It consists of two data sources:

 * Access log - Any incoming http request or connection (e.g., cURL) to a 3rd-party service. Incoming requests are logged by a http middleware. Outgoing calls are handled by a `App\Modules\History\Traits\Loggable` trait.
 * Change history - Any time a model is created, updated, or removed, the action is logged. This is handled by the `App\Modules\History\Traits\Historable` Eloquent trait applied to models.
