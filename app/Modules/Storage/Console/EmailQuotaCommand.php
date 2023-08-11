<?php

namespace App\Modules\Storage\Console;

use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Modules\Storage\Models\Notification;
use App\Modules\Storage\Models\Directory;
use App\Modules\Storage\Models\Usage;
use App\Modules\Storage\Models\StorageResource;
use App\Modules\Resources\Models\Asset;
use App\Modules\Storage\Mail\Quota;
use App\Modules\Users\Models\User;
use App\Halcyon\Utility\Number;
use Carbon\Carbon;

class EmailQuotaCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $signature = 'storage:emailquota
							{--debug : Do not perform actions, only report what will happen}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Email storage quota.';

	/**
	 * Execute the console command.
	 */
	public function handle(): int
	{
		$debug = $this->option('debug') ? true : false;

		$users = Notification::query()
			->select(DB::raw('DISTINCT(userid) AS userid'))
			->get()
			->pluck('userid')
			->toArray();

		if (!count($users))
		{
			if ($debug || $this->output->isVerbose())
			{
				$this->info('No quota notifications found.');
			}

			return Command::SUCCESS;
		}

		$n = (new Notification)->getTable();
		$d = (new Directory)->getTable();
		$r = (new Asset)->getTable();
		$s = (new StorageResource)->getTable();
		$total = 0;

		foreach ($users as $userid)
		{
			$user = User::find($userid);

			if (!$user)
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->error('Could not find account for user #' . $userid);
				}
				continue;
			}

			if (!$user->email)
			{
				if ($debug || $this->output->isVerbose())
				{
					$this->error("Email address not found for user {$user->name} ($userid).");
				}
				continue;
			}

			$notifications = Notification::query()
				->select($n . '.*')
				->withTrashed()
				->join($d, $d . '.id', $n . '.storagedirid')
				->join($r, $r . '.id', $d . '.resourceid')
				->join($s, $s . '.id', $d . '.storageresourceid')
				->where($n . '.userid', '=', $userid)
				->whereNull($n . '.datetimeremoved')
				->whereNull($d . '.datetimeremoved')
				->whereNull($r . '.datetimeremoved')
				->whereNull($s . '.datetimeremoved')
				->get();

			foreach ($notifications as $not)
			{
				if (!$not->wasNotified())
				{
					$not->datetimelastnotify = $not->datetimecreated;
				}

				$last = $not->directory->usage()
					->orderby('datetimerecorded', 'desc')
					->limit(1)
					->first();

				$not->status = '?';

				if ($last)
				{
					switch ($not->type->id)
					{
						case 1:
							$not->nextreport = $not->nextnotify;
						break;

						case 2:
							if (!$last->quota)
							{
								$not->status = '?';
							}
							else if ($last->space > $not->value)
							{
								$not->status = 0;
							}
							else
							{
								$not->status = 1;
							}
						break;

						case 3:
							if (!$last->quota)
							{
								$not->status = '?';
							}
							else if (($last->space / $last->quota) * 100 > $not->value)
							{
								$not->status = 0;
							}
							else
							{
								$not->status = 1;
							}
						break;

						case 4:
							if (!$last->quota)
							{
								$not->status = '?';
							}
							else if ($last->files > $not->value)
							{
								$not->status = 0;
							}
							else
							{
								$not->status = 1;
							}
						break;

						case 5:
							if (!$last->quota)
							{
								$not->status = '?';
							}
							else if (($last->files / $last->filequota) * 100 > $not->value)
							{
								$not->status = 0;
							}
							else
							{
								$not->status = 1;
							}
						break;

						default:
							// Nothing to do here
						break;
					}
				}
				else
				{
					$last = new Usage;
				}

				if ($not->type->id == 2)
				{
					$not->threshold = $not->formattedValue;
				}
				else if ($not->type->id == 3)
				{
					$not->threshold = $not->value . '%';
				}
				else if ($not->type->id == 4)
				{
					$not->threshold = $not->value . ' files';
				}
				else if ($not->type->id == 5)
				{
					$not->threshold = $not->value . '%';
				}

				// Exceeded quota
				if ($not->status === 0 && $not->notice == 0)
				{
					if ($not->enabled && $last && $last->space)
					{
						$total++;

						$message = new Quota('exceed', $user, $not, $last);
						$message->headers()->text([
							'X-Command' => 'storage:emailquota'
						]);

						if ($this->output->isDebug())
						{
							echo $message->render();
						}

						if ($debug || $this->output->isVerbose())
						{
							$this->comment('Emailed exceed quota for ' . $not->directory->fullPath . ' to ' . $user->email);

							if ($debug)
							{
								continue;
							}
						}

						Mail::to($user->email)->send($message);
					}

					// Attempt to prevent weird situations of resetting report date.
					if ($last && $last->space)
					{
						unset($not->status);
						unset($not->threshold);
						$not->datetimelastnotify = Carbon::now()->toDateTimeString();
						$not->notice = 1;
						$not->save();
					}
				}
				// Over threshold, have already notified. Nothing to do.
				else if ($not->status === 0 && $not->notice == 1)
				{
				}
				// Under threshold, haven't notified
				else if ($not->status === 1 && $not->notice == 1)
				{
					// Only mail if enabled
					if ($not->enabled)
					{
						/*$message = new Quota('below', $user, $not, $last);
						$message->headers()->text([
							'X-Command' => 'storage:emailquota'
						]);

						if ($debug)
						{
							//echo $message->render();
							$this->info('Emailed below quota to ' . $user->email);
							continue;
						}

						Mail::to($user->email)->send($message);*/
					}

					// Attempt to prevent weird situations of resetting report date.
					if ($last && $last->space > 0)
					{
						unset($not->status);
						unset($not->threshold);
						$not->datetimelastnotify = Carbon::now();
						$not->notice = 0;
						$not->save();
					}
				}
				// Under threshold, never notified or have notified. Nothing to do.
				else if ($not->status === 1 && $not->notice == 0)
				{
				}
				else
				{
					// Is usage report?
					if ($not->type->id != 1)
					{
						continue;
					}

					if (!$not->nextreport || date("U") <= strtotime($not->nextreport))
					{
						continue;
					}

					$storagedir = $not->directory;

					// Set notice
					if (strtotime($last->datetimerecorded) != 0
					 && $last->space != 0)
					{
						$not->datetimelastnotify = Carbon::now();

						// Only mail if enabled
						if ($not->enabled)
						{
							$total++;

							$message = new Quota('report', $user, $not, $last);
							$message->headers()->text([
								'X-Command' => 'storage:emailquota'
							]);

							if ($this->output->isDebug())
							{
								echo $message->render();
							}

							if ($debug || $this->output->isVerbose())
							{
								$this->info('Emailed report quota for ' . $not->directory->fullPath . ' to ' . $user->email . ', next report:' . $not->nextnotify);

								if ($debug)
								{
									continue;
								}
							}

							Mail::to($user->email)->send($message);
						}

						unset($not->status);
						unset($not->threshold);
						unset($not->nextreport);

						$not->save();

						// Attempt to prevent weird situations of resetting report date.
						/*if (strtotime($not->nextreport) > strtotime($not->datetimelastnotify))
						{
							unset($not->status);
							unset($not->threshold);
							unset($not->nextreport);
							$not->datetimelastnotify = $not->nextreport;
							$not->save();
						}
						else
						{
							$this->error('An error occurred: Tried to go backwards in time with quota report.');
						}*/
					}
				}
			}
		}

		if (!$total)
		{
			if ($debug || $this->output->isVerbose())
			{
				$this->info('No reports to send at this time.');
			}
		}

		return Command::SUCCESS;
	}
}
