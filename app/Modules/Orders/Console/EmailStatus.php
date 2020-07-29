<?php

namespace App\Modules\Orders\Console;

use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Modules\Orders\Models\Order;
use App\Modules\Users\Models\User;

class EmailStatus extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'orders:emailstatus';

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
		$this->info('Emailing order status...');
		return;
	}
}
