<?php

namespace App\Modules\Status\Console;

use Illuminate\Console\Command;
use App\Modules\Resources\Models\Type as AssetType;
use App\Modules\Status\Events\StatusRetrieval;
use Carbon\Carbon;

class FetchCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'status:fetch';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Fetch and cache status information from sources';

	/**
	 * Execute the console command.
	 *
	 * @return void
	 */
	public function handle()
	{
		$restypes = AssetType::query()
			->get();

		foreach ($restypes as $restype)
		{
			$resources = $restype->resources()
				->whereIsActive()
				->where('listname', '!=', '')
				->where('display', '>', 0)
				->orderBy('name', 'asc')
				->get();

			foreach ($resources as $resource)
			{
				$resource->statusUpdate = Carbon::now();

				event($event = new StatusRetrieval($resource));
			}
		}
	}

	/**
	 * Output help documentation
	 *
	 * @return  void
	 **/
	public function help()
	{
		$this->output
			 ->getHelpOutput()
			 ->addOverview('Fetch and cache status information from sources')
			 ->addTasks($this)
			 ->render();
	}
}
