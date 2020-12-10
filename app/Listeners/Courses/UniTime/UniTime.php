<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2005-2020 Purdue University
 * @license    http://opensource.org/licenses/MIT MIT
 */

namespace App\Listeners\Courses\UniTime;

use App\Modules\Courses\Events\AccountLookup;
use App\Modules\Courses\Events\AccountInstructorLookup;
use App\Modules\Courses\Events\InstructorLookup;
use App\Modules\Courses\Events\AccountEnrollment;
use App\Modules\Courses\Models\Account;
//use App\Modules\History\Models\Log;
use App\Modules\History\Traits\Loggable;
use GuzzleHttp\Client;
//use GuzzleHttp\Stream\PhpStreamRequestFactory;

/**
 * Course listener
 */
class UniTime
{
	use Loggable;

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
		$events->listen(InstructorLookup::class, self::class . '@handleInstructorLookup');
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
		$results = $this->request('instructor-schedule?term=' . $event->account->reference . '&id=' . $event->instructor->puid);
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
	 * Lookup courses for an instructor
	 *
	 * @param   object  $event
	 * @return  void
	 */
	public function handleInstructorLookup(InstructorLookup $event)
	{
		$results = $this->request('roles?id=' . $event->instructor->puid);

		if ($results['status'] == 500)
		{
			return;
		}

		$body = $results['body'];

		$semesters = array();
		foreach ($body as $semester)
		{
			if (strtotime($semester->endDate) >= date("U"))
			{
				if (in_array('Instructor', $semester->roles))
				{
					array_push($semesters, $semester);
				}
			}
		}

		$classes = array();

		if (count($semesters) > 0)
		{
			foreach ($semesters as $semester)
			{
				// Fetch instructor schedule
				$returned = $this->request('instructor-schedule?term=' . $semester->reference . '&id=' . $event->instructor->puid);

				if ($returned['status'] == 500)
				{
					continue;
				}

				$result = $returned['body'];
				$instructors = array();

				if (!isset($result->classes))
				{
					continue;
				}

				if (is_array($result))
				{
					foreach ($result->instructors as $instructor)
					{
						if (isset($instructor->email)) {
							array_push($instructors, $instructor);
						}
					}
				}
				else
				{
					if (isset($result->instructor->email))
					{
						array_push($instructors, $result->instructor);
					}
				}

				foreach ($result->classes as $class)
				{
					foreach ($class->course as $course)
					{
						// Skip known Thesis type courses
						if ($course->courseNumber == '69800'
						 || $course->courseNumber == '69900')
						{
							continue;
						}

						if (!isset($course->courseTitle))
						{
							$course->courseTitle = 'N/A';
						}

						if (preg_match("/Undergrad/", $course->courseTitle)
						 || preg_match("/Graduate/", $course->courseTitle))
						{
							continue;
						}

						$course->semester    = $semester->term . ' ' . $semester->year;
						$course->reference   = $semester->reference;
						$course->start       = $semester->beginDate;
						$course->stop        = $semester->endDate;
						$course->classId     = $class->classId;
						$course->instructors = $instructors;
						$course->students    = array();

						$result2 = $this->request("enrollments?classId=" . $class->classId);

						if ($result2['status'] == 200)
						{
							foreach ($result2['body'] as $student)
							{
								$name = '';

								if (isset($student->firstName))
								{
									$name .= $student->firstName . ' ';
								}

								if (isset($student->middleName))
								{
									$name .= $student->middleName . ' ';
								}

								if (isset($student->lastName))
								{
									$name .= $student->lastName;
								}

								array_push($course->students, urlencode($name));
							}
						}

						$enrollments = $this->request('enrollments?courseId=' . $course->courseId);

						if ($enrollments['status'] == 200)
						{
							$course->enrollment = $enrollments['body'];

							$course->student_list = array();

							if (is_array($course->enrollment))
							{
								foreach ($course->enrollment as $student)
								{
									if (isset($student->email) && $student->email)
									{
										array_push($course->student_list, $student->email);
									}
								}
							}

							$course->student_list = array_unique($course->student_list);
							sort($course->student_list);
						}

						array_push($classes, $course);
					}
				}
			}
		}

		$event->courses = $classes;
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
	private function request($url, $method = 'GET')
	{
		// Is the service configured?
		$config = config('listener.unitime', []);

		if (empty($config) || empty($config['user']))
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
					$config['dev'] ? $config['user_dev'] : $config['user'],
					$config['dev'] ? $config['password_dev'] : $config['password']
				],
				'stream' => true
				//'sink' => storage_path('unitime')//str_replace(['?', '='], '_', $url) . '.json')
			]);

			$status = $result->getStatusCode();
			$body   = json_decode($res->getBody()->getContents());
		}
		catch (\Exception $e)
		{
			$status = 500;
			$body   = ['error' => $e->getMessage()];
		}

		// Record the request
		/*Log::create([
			'ip'              => request()->ip(),
			'user'            => auth()->user()->id,
			'status'          => $status,
			'transportmethod' => 'GET',
			'servername'      => request()->getHttpHost(),
			'uri'             => $base . $url,
			'app'             => 'unitime',
			'payload'         => json_encode($body),
		]);*/

		$this->log('unittime', __METHOD__, $method, $status, $body, $base . $url);

		return [
			'status' => $status,
			'body'   => $body,
		];
	}
}
