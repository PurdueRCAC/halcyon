<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2005-2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Listeners\Courses\UniTime;

use App\Modules\Courses\Events\AccountLookup;
use App\Modules\Courses\Events\AccountInstructorLookup;
use App\Modules\Courses\Events\AccountEnrollment;
use App\Modules\Courses\Models\Account;
use App\Modules\History\Models\Log;
use GuzzleHttp\Client;

/**
 * Course listener
 */
class UniTime
{
	/**
	 * Production service URL
	 *
	 * @var  string
	 */
	private $url = 'https://timetable.mypurdue.purdue.edu/Timetabling/api/';

	/**
	 * Development/testing service URL
	 *
	 * @var  string
	 */
	private $url_dev = 'https://qaunitime.itap.purdue.edu/Timetabling/api/';

	/**
	 * Register the listeners for the subscriber.
	 *
	 * @param  Illuminate\Events\Dispatcher  $events
	 * @return void
	 */
	public function subscribe($events)
	{
		$events->listen(AccountLookup::class, self::class . '@handleAccountLookup');
		$events->listen(AccountInstructorLookup::class, self::class . '@handleAccountInstructorLookup');
		$events->listen(AccountEnrollment::class, self::class . '@handleAccountEnrollment');
	}

	/**
	 * Lookup a course
	 *
	 * @param   object  $event
	 * @return  void
	 */
	public function handleAccountLookup(AccountLookup $event)
	{
		if (!$event->account->classid)
		{
			return;
		}

		$results = $this->request('class-info?classId=' . $event->account->classid);
		$matched = false;

		if (!empty($results) && $results['status'] == 200)
		{
			$body = $results['body'];

			// Find appropriate section
			foreach ($body->course as $course)
			{
				if ($course->classExternalId == $event->account->crn)
				{
					$matched = true;

					$event->account->department   = $course->subjectArea;
					$event->account->coursenumber = $course->courseNumber;
					$event->account->classname    = $course->courseTitle;
				}
			}
		}

		if (!$matched)
		{
			$event->account->cn = false;
		}
	}

	/**
	 * Lookup courses for an instructor
	 *
	 * @param   object  $event
	 * @return  void
	 */
	public function handleAccountInstructorLookup(AccountInstructorLookup $event)
	{
		$results = $this->request('instructor-schedule?term=' . $event->account->reference . '&id=' . $event->instructor->organization_id);
		$matched = false;

		if (!empty($results) && $results['status'] == 200)
		{
			$body = $results['body'];

			foreach ($body->classes as $class)
			{
				foreach ($class->course as $course)
				{
					if ($course->classExternalId == $event->account->crn)
					{
						$matched = true;

						$event->account->classid = $course->courseId;
					}
				}
			}
		}

		if (!$matched)
		{
			$event->account->cn = false;
		}
	}

	/**
	 * Get course enrollment
	 *
	 * @param   object  $event
	 * @return  void
	 */
	public function handleAccountEnrollment(AccountEnrollment $event)
	{
		$results = $this->request('enrollments?courseId=' . $event->account->classid);

		if (empty($results))
		{
			return;
		}

		if ($results['status'] == 200)
		{
			$event->enrollments = $results['body'];
		}
	}

	/**
	 * Make a request to the external service
	 *
	 * @param   string  $url
	 * @param   string  $method
	 * @return  array
	 */
	private function request($url, $method == 'GET')
	{
		// Is the service configured?
		$config = config('listener.unitime', []);

		if (empty($config))
		{
			return [];
		}

		// Get base service URL
		$base = $config['dev'] ? $this->url_dev : $this->url;

		try
		{
			$client = new Client();

			$result = $client->request($method, $base . $url, [
				'auth' => [
					$config['user'],
					$config['password']
				]
			]);

			$status = $result->getStatusCode();
			$body   = $result->getBody();
		}
		catch (\Exception $e)
		{
			$status = 500;
			$body   = ['error' => $e->getMessage()];
		}

		// Record the request
		Log::create([
			'ip'              => request()->ip(),
			'user'            => auth()->user()->id,
			'status'          => $status,
			'transportmethod' => 'GET',
			'servername'      => request()->getHttpHost(),
			'uri'             => $base . $url,
			'app'             => 'unitime',
			'payload'         => json_encode($body),
		]);

		return [
			'status' => $status,
			'body'   => $body,
		];
	}
}
