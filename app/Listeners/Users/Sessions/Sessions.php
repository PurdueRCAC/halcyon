<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Listeners\Users\Sessions;

use App\Modules\Users\Events\UserDeleted;
use Illuminate\Support\Facades\DB;

/**
 * User listener for sessions
 */
class Sessions
{
	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(UserDeleted::class, self::class . '@handleUserDeleted');
	}

	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   object   $article  The article object.  Note $article->text is also available
	 * @return  void
	 */
	public function handleUserDeleted(UserDeleted $event)
	{
		DB::table('sessions')
			->where('user_id', '=', $event->user->id)
			->delete();
	}
}
