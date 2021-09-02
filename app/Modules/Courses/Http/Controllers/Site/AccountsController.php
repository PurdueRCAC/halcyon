<?php

namespace App\Modules\Courses\Http\Controllers\Site;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Fluent;
use Illuminate\Support\Facades\Storage;
use App\Modules\Courses\Models\Account;
use App\Modules\Courses\Models\Member;
use App\Modules\Users\Models\UserUsername;
use App\Modules\Users\Models\User;

class AccountsController extends Controller
{
	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  Request $request
	 * @return Response
	 */
	public function export(Request $request)
	{
		$filename = $request->input('filename', 'data') . '.csv';

		$headers = array(
			'Content-type' => 'text/csv',
			'Content-Disposition' => 'attachment; filename=' . $filename,
			'Pragma' => 'no-cache',
			'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
			'Expires' => '0'
		);

		if ($data = $request->input('data'))
		{
			$data = json_decode(urldecode($data));
		}
		else
		{
			$data = array(
				array(
					'Class Account ID',
					'User ID',
					'Name',
					'Username',
					'Added',
					'Start',
					'Stop',
					'Removed'
				)
			);
			$class = Account::findOrFail($request->input('id'));

			$m = (new Member)->getTable();
			$u = (new UserUsername)->getTable();

			$members = $class->members()
				->select($m . '.*')
				->leftJoin($u, $u . '.userid', $m . '.userid')
				->withTrashed()
				->whereIsActive()
				->where('membertype', '>=', 0)
				->orderBy($m . '.membertype', 'desc')
				->orderBy($u . '.username', 'asc')
				->get();

			if (count($members))
			{
				foreach ($members as $usr)
				{
					if (!$usr->user || !$usr->user->id)
					{
						continue;
					}

					$data[] = array(
						$usr->classaccountid,
						$usr->userid,
						$usr->user->name,
						$usr->user->username,
						$usr->datetimecreated,
						$usr->datetimestart,
						$usr->datetimestop,
						($usr->isTrashed() ? $usr->datetimeremoved : '')
					);
				}
			}
		}

		$callback = function() use ($data)
		{
			$file = fopen('php://output', 'w');

			if (is_array($data))
			{
				foreach ($data as $datum)
				{
					fputcsv($file, $datum);
				}
			}
			fclose($file);
		};

		return response()->streamDownload($callback, $filename, $headers);
	}

	/**
	 * Import
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function import(Request $request)
	{
		$id = $request->input('id');

		$class = Account::findOrFail($id);

		$disk = 'local';
		$files = $request->file();

		if (empty($files))
		{
			abort(415, trans('courses::courses.error.no files'));
		}

		$response = [
			'data' => array(),
			'error' => array()
		];

		$row = 0;
		$headers = array();
		$data = array();

		$fileNotUploaded = false;
		$maxSize = config('module.courses.max-file-size', 0);
		$allowedTypes = config('module.courses.allowed-extensions', ['csv']);

		foreach ($files as $file)
		{
			// Check file size
			if (($maxSize && $file->getSize() / 1024 > $maxSize)
			 || $file->getSize() / 1024 > $file->getMaxFilesize())
			{
				$fileNotUploaded = true;
				continue;
			}

			// Check allowed file type
			// Doing this by file extension is iffy at best but
			// detection by contents produces `txt`
			if (!empty($allowedTypes)
			 && !in_array($file->getClientOriginalExtension(), $allowedTypes))
			{
				$fileNotUploaded = true;
				continue;
			}

			// Save file
			$path = $file->store('temp');

			try
			{
				// Get file data and process into a collection of objects
				$handle = fopen(storage_path('app/' . $path), 'r');

				if ($handle !== false)
				{
					while (!feof($handle))
					{
						$line = fgetcsv($handle, 0, ',');

						if ($row == 0)
						{
							$headers = $line;
							$row++;
							continue;
						}

						$item = new Fluent;
						foreach ($headers as $k => $v)
						{
							$v = strtolower($v);
							//$v = preg_replace('/[^a-z0-9]/', '', $v);

							$item->{$v} = $line[$k];
						}

						$data[] = $item;

						$row++;
					}
					fclose($handle);
				}

				$data = collect($data);

				foreach ($data as $item)
				{
					if (!$item->username
					 && !$item->email)
					{
						continue;
					}

					// See if an account already exists
					// Create if not
					if (!$item->username && $item->email)
					{
						$item->username = strstr($item->email, '@', true);
					}

					$user = User::createFromUsername($item->username);

					if (!$user || !$user->id)
					{
						// Something went wrong
						throw new \Exception(trans('courses::courses.error.entry failed for user', ['name' => $item->username]));
					}

					// See if membership already exists
					$member = Member::query()
						->withTrashed()
						->where('userid', '=', $user->id)
						->where('classaccountid', '=', $class->id)
						->first();

					if ($member)
					{
						// Was apart of the class but membership was removed?
						if ($member->isTrashed())
						{
							// Restore membership
							$member->forceRestore();
						}

						// Already apart of the class
						continue;
					}

					// Create the membership
					$member = new Member;
					$member->classaccountid = $class->id;
					$member->datetimestart = $class->datetimestart;
					$member->datetimestop = $class->datetimestop;
					$member->userid = $user->id;
					$member->save();

					$response['data'][] = $member->toArray();
				}
			}
			catch (\Exception $e)
			{
				$response['error'][] = $e->getMessage();
				$fileNotUploaded = false;
			}

			// Clean up
			Storage::disk($disk)->delete($path);
		}

		if ($fileNotUploaded)
		{
			$response['message'] = trans('courses::courses.error.not all uploaded');
		}

		return redirect(route('site.users.account'));
	}
}
