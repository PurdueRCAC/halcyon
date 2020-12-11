<?php

namespace App\Modules\Cron\Models;

use App\Halcyon\Models\Casts\Params;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Command\Command;
use App\Modules\History\Traits\Historable;
use Cron\CronExpression;
use Carbon\Carbon;

/**
 * Cron model for a job
 */
class Job extends Model
{
	use Historable;

	/**
	 * The table to which the class pertains
	 *
	 * @var  string
	 **/
	protected $table = 'cron_jobs';

	/**
	 * Cron expression
	 *
	 * @var  object
	 */
	protected $expression = null;

	/**
	 * Default order by for model
	 *
	 * @var string
	 */
	public $orderBy = 'command';

	/**
	 * Default order direction for select queries
	 *
	 * @var  string
	 */
	public $orderDir = 'asc';

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'run_at' => 'date',
		'state' => 'integer',
		'active' => 'integer',
		'dont_overlap' => 'integer',
		'params' => Params::class,
	];

	/**
	 * Fields and their validation criteria
	 *
	 * @var array
	 */
	protected $rules = [
		'name' => 'required|string|max:255',
		'command' => 'required|string|max:255',
		'parameters' => 'nullable|string|max:255',
		'state' => 'nullable|in:0,1',
		'dont_overlap' => 'nullable|in:0,1',
		'recurrence' => 'required|string',
	];

	/**
	 * Runs extra setup code when creating a new model
	 *
	 * @return  void
	 */
	public function setup()
	{
		$this->addRule('recurrence', function($data)
		{
			$data['recurrence'] = preg_replace('/[\s]{2,}/', ' ', $data['recurrence']);

			if (preg_match('/[^-,*\/ \\d]/', $data['recurrence']) !== 0)
			{
				return trans('Cron String contains invalid character.');
			}

			$bits = @explode(' ', $data['recurrence']);
			if (count($bits) != 5)
			{
				return trans('Cron string is invalid. Too many or too little sections.');
			}

			return false;
		});
	}

	/**
	 * Get a cron expression
	 *
	 * @return  object
	 */
	public function expression()
	{
		if (!($this->expression instanceof CronExpression))
		{
			$this->expression = CronExpression::factory($this->recurrence);
		}
		return $this->expression;
	}

	/**
	 * Is the entry published?
	 *
	 * @return  boolean
	 */
	public function isPublished()
	{
		return ($this->state == 1);
	}

	/**
	 * Check if the job is available
	 *
	 * @return  boolean
	 */
	public function isAvailable()
	{
		// If it doesn't exist or isn't published
		if (!$this->id || !$this->isPublished())
		{
			return false;
		}

		// Make sure the item is published and within the available time range
		if ($this->started() && !$this->ended())
		{
			return true;
		}

		return false;
	}

	/**
	 * Get the last run timestamp
	 *
	 * @param   string  $format
	 * @return  string
	 */
	public function lastRun($format = 'Y-m-d H:i:s')
	{
		return $this->expression()->getPreviousRunDate()->format($format);
	}

	/**
	 * Get the next run timestamp
	 *
	 * @param   string  $format
	 * @return  string
	 */
	public function nextRun($format = 'Y-m-d H:i:s')
	{
		return $this->expression()->getNextRunDate()->format($format);
	}

	/**
	 * Get minutes
	 *
	 * @return string
	 */
	public function getMinuteAttribute()
	{
		return $this->parseExpression('minute');
	}

	/**
	 * Get hour
	 *
	 * @return string
	 */
	public function getHourAttribute()
	{
		return $this->parseExpression('hour');
	}

	/**
	 * Get day
	 *
	 * @return string
	 */
	public function getDayAttribute()
	{
		return $this->parseExpression('day');
	}

	/**
	 * Get month
	 *
	 * @return string
	 */
	public function getMonthAttribute()
	{
		return $this->parseExpression('month');
	}

	/**
	 * Get day of the week
	 *
	 * @return string
	 */
	public function getDayofweekAttribute()
	{
		return $this->parseExpression('dayofweek');
	}

	/**
	 * Is this a custom cron statement?
	 *
	 * @return bool
	 */
	public function isCustomRecurrence()
	{
		$defaults = array(
			'',
			'0 0 1 1 *',
			'0 0 1 * *',
			'0 0 * * 0',
			'0 0 * * *',
			'0 * * * *'
		);

		return !in_array($this->recurrence, $defaults);
	}

	/**
	 * Get a portion of a cron statement
	 *
	 * @param  string  $key
	 * @return array
	 */
	private function parseExpression($key)
	{
		$parts = $this->parts;

		if (empty($parts))
		{
			$parts = [
				'minute'    => '*',
				'hour'      => '*',
				'day'       => '*',
				'month'     => '*',
				'dayofweek' => '*',
			];

			if ($this->recurrence)
			{
				$bits = explode(' ', $this->recurrence);

				$parts['minute']    = $bits[0];
				$parts['hour']      = $bits[1];
				$parts['day']       = $bits[2];
				$parts['month']     = $bits[3];
				$parts['dayofweek'] = $bits[4];
			}
		}

		return $parts[$key];
	}

	 /**
	 * Return collection of Artisan commands filtered if needed.
	 *
	 * @return \Illuminate\Support\Collection
	 */
	public static function getCommands()
	{
		$command_filter = config('module.cron.artisan.command_filter');
		$whitelist      = config('module.cron.artisan.whitelist', true);

		$all_commands = collect(Artisan::all());

		if (! empty($command_filter))
		{
			$all_commands = $all_commands->filter(function (Command $command) use ($command_filter, $whitelist)
			{
				foreach ($command_filter as $filter)
				{
					if (fnmatch($filter, $command->getName()))
					{
						return $whitelist;
					}
				}

				return ! $whitelist;
			});
		}

		return $all_commands->sortBy(function (Command $command)
		{
			$name = $command->getName();

			if (mb_strpos($name, ':') === false)
			{
				$name = ':' . $name;
			}

			return $name;
		});
	}
}
