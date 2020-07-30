<?php

namespace App\Modules\Storage\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Facades\Mail;
use App\Modules\Storage\Models\Directory;
use App\Modules\Storage\Mail\Quota;
use App\Modules\Users\Models\User;

class EmailQuotaCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'storage:emailquota';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Email storage quota.';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		//$directory = new Directory;
		// Prepare and send actual email
		//Mail::to($user->email)->send(new Quota($comment));
		//echo (new Quota($directory))->render();

		$this->info("Emailing quota...");
	}
}
