<?php

namespace App\Modules\Resources\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Modules\Resources\Models\Subresource;
use App\Modules\Resources\Models\Child;
use App\Modules\Resources\Models\Asset;
use App\Modules\Queues\Models\Scheduler;
use App\Modules\Storage\Models\StorageResource;

class CopyCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $signature = 'resources:copy {id} {--allocations} {--debug : Output emails rather than sending}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Copy a resource and its associated data. Optionally copy all allocations.';

	/**
	 * Execute the console command.
	 */
	public function handle(): int
	{
		$debug = $this->option('debug') ? true : false;
		$allocations = $this->option('allocations');

		$id = intval($this->argument('id'));

		$asset = Asset::find($id);

		if (!$asset || !$asset->id)
		{
			$this->error('Failed to find resource entry for ID #' . $id);
			return Command::FAILURE;
		}

		if ($debug || $this->output->isVerbose())
		{
			$this->info('Copying ' . $asset->name . ' (#' . $id . ') ...');
		}

		$data = $asset->getAttributes();// $asset->replicate();
		unset($data['id']);
		$copy = $asset->newInstance($data);
		$copy->name = $asset->name . ' copy';

		if ($asset->rolename)
		{
			$copy->rolename = $asset->rolename . '_copy';
		}
		if ($asset->listname)
		{
			$copy->listname = $asset->listname . '_copy';
		}
		if (!$debug)
		{
			$copy->saveQuietly();
		}

		foreach ($asset->facets as $facet)
		{
			if ($debug || $this->output->isVerbose())
			{
				$this->line('   - Copying facet ID #' . $facet->id . ' ...');

				if ($debug)
				{
					continue;
				}
			}

			$copyfacet = $facet->replicate();
			$copyfacet->asset_id = $copy->id;
			$copyfacet->save();
		}

		foreach ($asset->subresources()->orderBy('id', 'asc')->get() as $i => $subresource)
		{
			if ($debug || $this->output->isVerbose())
			{
				$this->line('Copying subresource ' .  $subresource->name . ' (#' . $subresource->id . ') ...');
			}

			$data = $subresource->toArray();
			unset($data['id']);
			unset($data['datetimecreated']);
			$copysubresource = $subresource->newInstance($data);
			$copysubresource->name .= ' copy';

			if (!$debug)
			{
				$copysubresource->saveQuietly();

				$child = new Child;
				$child->subresourceid = $copysubresource->id;
				$child->resourceid = $copy->id;
				$child->save();
			}

			if ($debug || $this->output->isVerbose())
			{
				$this->line('Copying subresource ' .  $subresource->name . ' (#' . $subresource->id . ') schedulers ...');
			}

			$schedulers = Scheduler::query()
				->where('queuesubresourceid', '=', $subresource->id)
				->get();

			if (count($schedulers) > 0)
			{
				foreach ($schedulers as $scheduler)
				{
					$data = $scheduler->toArray();
					unset($data['id']);
					$copyscheduler = $scheduler->newInstance($data); //->replicate();
					$copyscheduler->queuesubresourceid = $copysubresource->id;
					$copyscheduler->hostname = str_replace($asset->rolename, $copy->rolename, $copyscheduler->hostname);
					if (!$debug)
					{
						$copyscheduler->save();
					}
				}
			}
			elseif ($i == 0)
			{
				$copyscheduler = new Scheduler;
				$copyscheduler->hostname = $copy->rolename . '-adm.' . str_replace('www', '', request()->getHost());
				$copyscheduler->queuesubresourceid = $copysubresource->id;
				$copyscheduler->batchsystem = $copy->batchsystem;
				$copyscheduler->schedulerpolicyid = 1;
				$copyscheduler->defaultmaxwalltime = 1209600;
				$copyscheduler->save();
			}

			if ($allocations)
			{
				foreach ($subresource->queues as $queue)
				{
					$data = $queue->toArray();
					unset($data['id']);
					$data['schedulerid'] = $copyscheduler->id;
					$copyqueue = $queue->newInstance($data);//->replicate();

					$copyqueue->subresourceid = $copysubresource->id;
					if ($debug || $this->output->isVerbose())
					{
						$this->line('Copying queue ' .  $queue->name . ' (#' . $queue->id . ') ...');
					}
					if (!$debug)
					{
						$copyqueue->saveQuietly();
					}

					if ($debug || $this->output->isVerbose())
					{
						$this->line('   - Copying queue ' .  $queue->name . ' (#' . $queue->id . ') walltimes');
					}
					foreach ($queue->walltimes as $walltime)
					{
						$data = $walltime->toArray();
						unset($data['id']);
						$copywalltime = $walltime->newInstance($data); //->replicate();
						$copywalltime->queueid = $copyqueue->id;
						if (!$debug)
						{
							$copywalltime->save();
						}
					}

					if ($debug || $this->output->isVerbose())
					{
						$this->line('   - Copying queue ' .  $queue->name . ' (#' . $queue->id . ') users');
					}
					foreach ($queue->users as $quser)
					{
						$data = $quser->getAttributes();
						unset($data['id']);
						$copyquser = $quser->newInstance($data); //->replicate();
						$copyquser->queueid = $copyqueue->id;
						if (!$debug)
						{
							$copyquser->save();
						}
					}

					if ($debug || $this->output->isVerbose())
					{
						$this->line('   - Copying queue ' .  $queue->name . ' (#' . $queue->id . ') purchases');
					}
					foreach ($queue->sizes as $size)
					{
						$data = $size->getAttributes();
						unset($data['id']);
						$copysize = $size->newInstance($data); //->replicate();
						$copysize->queueid = $copyqueue->id;
						if (!$debug)
						{
							$copysize->saveQuietly();
						}
					}
					foreach ($queue->sold as $size)
					{
						$data = $size->getAttributes();
						unset($data['id']);
						$copysize = $size->newInstance($data);//->replicate();
						$copysize->sellerqueueid = $copyqueue->id;
						if (!$debug)
						{
							$copysize->saveQuietly();
						}
					}

					if ($debug || $this->output->isVerbose())
					{
						$this->line('   - Copying queue ' .  $queue->name . ' (#' . $queue->id . ') loans');
					}
					foreach ($queue->loans as $loan)
					{
						$data = $loan->getAttributes();
						unset($data['id']);
						$copyloan = $loan->newInstance($data); //->replicate();
						$copyloan->queueid = $copyqueue->id;
						if (!$debug)
						{
							$copyloan->saveQuietly();
						}
					}
					foreach ($queue->loaned as $loan)
					{
						$data = $loan->getAttributes();
						unset($data['id']);
						$copyloan = $loan->newInstance($data); //->replicate();
						$copyloan->lenderqueueid = $copyqueue->id;
						if (!$debug)
						{
							$copyloan->saveQuietly();
						}
					}
				}
			}
		}

		// TODO: Copy storage purchases/loans
		$storages = StorageResource::query()
			->where('parentresourceid', '=', $asset->id)
			->get();

		foreach ($storages as $storage)
		{
			$data = $storage->getAttributes();
			unset($data['id']);
			unset($data['datetimecreated']);
			$copystorage = $storage->newInstance($data); //->replicate();
			$copystorage->name .= ' copy';

			if ($debug || $this->output->isVerbose())
			{
				$this->line('Copying storage resource ' .  $storage->name . ' (#' . $storage->id . ') ...');
			}

			if (!$debug)
			{
				$copystorage->save();
			}

			if ($allocations)
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->line('   - Copying storage resource ' .  $storage->name . ' (#' . $storage->id . ') directories');
				}

				foreach ($storage->directories()->where('parentstoragedirid', '=', 0)->get() as $directory)
				{
					$data = $directory->getAttributes();
					unset($data['id']);
					unset($data['datetimecreated']);
					$copydirectory = $directory->newInstance($data); //->replicate();

					if (!$debug)
					{
						$copydirectory->saveQuietly();
					}
				}
			}
		}

		return Command::SUCCESS;
	}
}
