<?php

namespace App\Modules\Storage\Console;

use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Modules\Storage\Models\Notification;
use App\Modules\Storage\Models\Directory;
use App\Modules\Storage\Models\Usage;
use App\Modules\Storage\Mail\Quota;
use App\Modules\Users\Models\User;
use App\Halcyon\Utility\Number;

class EmailQuotaCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $signature = 'storage:emailquota {--debug}';

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
		$debug = $this->option('debug') ? true : false;

		$users = Notification::query()
			->select(DB::raw('DISTINCT(userid) AS userid'))
			->get()
			->pluck('userid')
			->toArray();

		if (!count($users))
		{
			$this->info("No quotas found");
			return;
		}

		$n = (new Notification)->getTable();
		$d = (new Directory)->getTable();

		foreach ($users as $userid)
		{
			$user = User::find($userid);

			if (!$user)
			{
				$this->error('Could not account for user #' . $userid);
				continue;
			}

			$notifications = Notification::query()
				->join($d, $d . '.id', $n . '.storagedirid')
				->where($n . '.userid', '=', $userid)
				->where(function($where) use ($n)
				{
					$where->whereNull($n . '.datetimeremoved')
						->orWhere($n . '.datetimeremoved', '=', '0000-00-00 00:00:00');
				})
				->where(function($where) use ($d)
				{
					$where->whereNull($d . '.datetimeremoved')
						->orWhere($d . '.datetimeremoved', '=', '0000-00-00 00:00:00');
				})
				->get();

			foreach ($notifications as $not)
			{
				if (!$not->datetimelastnotify || $not->datetimelastnotify == '0000-00-00 00:00:00')
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
					$not->threshold = Number::formatBytes($not->value);
				}
				else if ($not->type->id == 3)
				{
					$not->threshold = $not->value . '%';
				}
				else if ($not->type->id == 4)
				{
					$not->threshold = $not->value . " files";
				}
				else if ($not->type->id == 5)
				{
					$not->threshold = $not->value . '%';
				}

				if ($not->status == 0 && $not->notice == 0)
				{
					$message = new Quota('exceed', $user, $not, $last);

					if ($debug)
					{
						echo $message->render();
						continue;
					}

					Mail::to($user->email)->send($message);

					$this->info('Emailed exceed quota to ' . $user->email);
				}
				// Over threshold, have already notified. Nothing to do.
				else if ($not->status == 0 && $not->notice == 1)
				{
				}
				// Under threshold, haven't notified
				else if ($not->status == 1 && $not->notice == 1)
				{
					$message = new Quota('below', $user, $not, $last);

					if ($debug)
					{
						echo $message->render();
						continue;
					}

					Mail::to($user->email)->send($message);

					$this->info('Emailed below quota to ' . $user->email);
				}
				// Under threshold, never notified or have notified. Nothing to do.
				else if ($not->status == 1 && $not->notice == 0)
				{
				}
				else
				{
					if ($not->type->id == 1)
					{
						if (date("U") > strtotime($not->nextreport))
						{
							$storagedir = $not->directory;

							// Set notice
							if (strtotime($last->datetimerecorded) != 0
							 && $last->space != 0)
							{
								// Only mail if enabled
								if ($not->enabled)
								{
									$message = new Quota('report', $user, $not, $last);

									if ($debug)
									{
										echo $message->render();
										$message = $message->build();
										continue;
									}

									Mail::to($user->email)->send($message);

									$this->info('Emailed report quota to ' . $user->email);
								}

								// Attempt to prevent weird situations of resetting report date.
								if (strtotime($not->nextreport) > strtotime($not->datetimelastnotify))
								{
									$not->datetimelastnotify = $not->nextreport;
									$not->save();
								}
								else
								{
									$this->error('An error occurred: Tried to go backwards in time with quota report.');
								}
							}
						}
					}
				}
			}
		}

		$this->info("Emailing quota...");
	}
}
