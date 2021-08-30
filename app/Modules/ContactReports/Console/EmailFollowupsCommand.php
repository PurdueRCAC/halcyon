<?php

namespace App\Modules\ContactReports\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Modules\History\Models\Log;
use App\Modules\ContactReports\Mail\Followup;
use App\Modules\ContactReports\Models\Report;
use App\Modules\ContactReports\Models\Type;
use App\Modules\ContactReports\Models\User as CrmUser;
use App\Modules\Users\Models\User;
use Carbon\Carbon;

class EmailFollowupsCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'crm:emailfollowups {--debug : Output emails rather than sending}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Email a followup to a user from a Contact Report.';

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		$debug = $this->option('debug') ? true : false;

		// Get all new comments
		$types = Type::where('timeperiodid', '>', 0)
			->where('timeperiodcount', '>', 0)
			->get();

		if (!count($types))
		{
			if ($debug)
			{
				$this->comment('No contact report types are configured for followups.');
			}
			return;
		}

		$users = [];
		$now = Carbon::now();
		$total = 0;

		foreach ($types as $type)
		{
			$emailed = array();

			// Only process items that are a certain age (e.g., 14 days old)
			//
			// We do this to account for any delays in registering contact reports
			// It's possible a contact happened on a Thursday and doesn't get
			// reported until the following Monday. If the timeperiod is 2 days,
			// it'd never get processed. So, we look further back in time.
			$dt = Carbon::now();
			$threshold = $dt->modify('-' . $type->timeperiodlimit . ' ' . $type->timeperiod->plural)->toDateTimeString();

			$r = (new Report)->getTable();
			$cu = (new CrmUser)->getTable();

			$users = CrmUser::query()
				->join($r, $r . '.id', $cu . '.contactreportid')
				->select($cu . '.*', $r . '.datetimecontact')
				->where($r . '.contactreporttypeid', '=', $type->id)
				->where($r . '.datetimecontact', '>=', $threshold)
				->where(function($where) use ($cu)
				{
					$where->whereNull($cu . '.datetimelastnotify')
						->orWhere($cu . '.datetimelastnotify', '=', '0000-00-00 00:00:00');
				})
				->get();

			// Any records found?
			if (count($users) == 0)
			{
				continue;
			}

			// Send email to each subscriber
			foreach ($users as $u)
			{
				if (in_array($u->userid, $emailed))
				{
					continue;
				}

				// Did we find an active account?
				$user = $u->user;

				if (!$user || !$user->id || $user->isTrashed())
				{
					continue;
				}

				// We only want items that have been at least `timeperiodcount` after `datetimecontact`
				$dt = Carbon::parse($u->datetimecontact);
				$delay = $dt->modify('+' . $type->timeperiodcount . ' ' . $type->timeperiod->plural);

				if ($delay->getTimestamp() > $now->getTimestamp())
				{
					continue;
				}

				if ($type->waitperiodcount && $type->waitperiodid)
				{
					// Check if they were followed up on any other Contact Reports in the last X time
					$lastfollowup = CrmUser::query()
						->join($r, $r . '.id', $cu . '.contactreportid')
						->where($r . '.contactreporttypeid', '=', $type->id)
						->where($cu . '.userid', '=', $u->userid)
						->whereNotNull($cu . '.datetimelastnotify')
						->where($cu . '.datetimelastnotify', '!=', '0000-00-00 00:00:00')
						->where($cu . '.datetimelastnotify', '>', Carbon::now()->modify('-' . $type->waitperiodcount . ' ' . $type->waitperiod->plural)->toDateTimeString())
						->count();

					if ($lastfollowup)
					{
						continue;
					}
				}

				// Prepare and send actual email
				$emailed[] = $user->id;

				$message = new Followup($type, $u);

				if ($debug)
				{
					echo $message->render();
					$this->info("Emailed {$type->name} followup to {$user->email}.");
					continue;
				}

				Mail::to($user->email)->send($message);

				$this->log($user->id, $type->id, $user->email, "Emailed {$type->name} followup.");

				// Update the record
				$u->update(['datetimelastnotify' => $now->toDateTimeString()]);

				$total++;
			}
		}

		if ($debug && !$total)
		{
			$this->comment('No followups sent.');
		}
	}

	/**
	 * Log email
	 *
	 * @param   integer $targetuserid
	 * @param   integer $targetobjectid
	 * @param   string  $uri
	 * @param   mixed   $payload
	 * @return  null
	 */
	protected function log($targetuserid, $targetobjectid, $uri = '', $payload = '')
	{
		Log::create([
			'ip'              => request()->ip(),
			'userid'          => (auth()->user() ? auth()->user()->id : 0),
			'status'          => 200,
			'transportmethod' => 'POST',
			'servername'      => request()->getHttpHost(),
			'uri'             => Str::limit($uri, 128, ''),
			'app'             => Str::limit('email', 20, ''),
			'payload'         => Str::limit($payload, 2000, ''),
			'classname'       => Str::limit('crm:emailfollowups', 32, ''),
			'classmethod'     => Str::limit('handle', 16, ''),
			'targetuserid'    => (int)$targetuserid,
			'targetobjectid'  => (int)$targetobjectid,
			'objectid'        => (int)$targetobjectid,
		]);
	}
}
