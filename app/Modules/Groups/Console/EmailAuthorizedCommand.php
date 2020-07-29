<?php

namespace App\Modules\Groups\Console;

use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Modules\Groups\Models\Group;
use App\Modules\Groups\Models\Member;
use App\Modules\Users\Models\User;

class EmailAuthorizedCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'groups:emailauthorized';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Email latest Contact Report comments to subscribers.';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		// Get all new comments
		$comments = Comment::where('notice', '!=', 0)->get();
		//$comments = Comment::where('notice', '=', 22)->get();

		if (!count($comments))
		{
			$this->comment('No new comments to email.');
			return;
		}

					// Prepare and send actual email
					//Mail::to($user->email)->send(new NewComment($comment));
					//echo (new NewComment($comment))->render();

					$this->info("Emailed comment #{$comment->id} to {$user->email}.");
	}
}
