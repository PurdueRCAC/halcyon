<?php

namespace App\Modules\Courses\Http\Controllers\Site;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Modules\Courses\Models\Account;

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

			$members = $class->members()
				->withTrashed()
				->whereIsActive()
				->where('membertype', '>=', 0)
				->orderBy('datetimecreated', 'asc')
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
}
