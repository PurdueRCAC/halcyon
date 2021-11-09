<?php

namespace App\Modules\Users\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap any module channels.
	 *
	 * @return void
	 */
	public function boot()
	{
		Broadcast::routes();

		require dirname(__DIR__) . '/Routes/channels.php';
	}
}
