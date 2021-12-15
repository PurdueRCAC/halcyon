<?php

namespace App\Modules\History\Listeners;

use Illuminate\Console\Events\CommandFinished;
use App\Modules\History\Models\Log;

/**
 * Listener for artisan commands
 */
class LogCommand
{
	/**
	 * Plugin that loads module positions within content
	 *
	 * @param   object   $event
	 * @return  void
	 */
	public function handle(CommandFinished $event)
	{
		if ($this->shouldIgnore($event))
		{
			return;
		}

		Log::create([
			'ip'              => '127.0.0.1',
			'userid'          => (auth()->user() ? auth()->user()->id : 0),
			'status'          => $event->exitCode > 0 ? 500 : 200,
			'transportmethod' => '',
			'servername'      => 'localhost',
			'uri'             => $event->command ?? $event->input->getArguments()['command'] ?? 'default',
			'app'             => 'cli',
			'payload'         => json_encode($event->input->getArguments() + $event->input->getOptions()),
			'classname'       => 'artisan',
			'classmethod'     => 'handle',
		]);
	}

	/**
	 * Determine if the event should be ignored.
	 *
	 * @param  mixed  $event
	 * @return bool
	 */
	private function shouldIgnore($event)
	{
		return in_array($event->command, array_merge(config('module.history.ignore_commands', []), [
			'schedule:run',
			'schedule:finish',
			'package:discover',
		]));
	}
}
