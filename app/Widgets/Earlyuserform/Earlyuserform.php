<?php
namespace App\Widgets\Earlyuserform;

use Illuminate\Support\Facades\Mail;
use App\Modules\Widgets\Entities\Widget;
use App\Widgets\Earlyuserform\Mail\Application;
use App\Widgets\Earlyuserform\Mail\Confirmation;
use App\Modules\Resources\Models\Asset;

/**
 * Display an Early User application form
 */
class Earlyuserform extends Widget
{
	/**
	 * Display
	 *
	 * @return  void
	 */
	public function run()
	{
		$errors = array();
		$data = array();

		if (request()->method() == 'POST')
		{
			$data = request()->input('apply', []);

			if (!isset($data['name']))
			{
				$errors[] = trans('Please provide a name.');
			}

			if (!isset($data['email']))
			{
				$errors[] = trans('Please provide a valid email.');
			}

			if (!isset($data['institution']))
			{
				$errors[] = trans('Please provide an institution.');
			}

			if (!isset($data['domain']))
			{
				$errors[] = trans('Please provide a domain.');
			}

			if (empty($errors))
			{
				// Prepare and send actual email
				$destination = $this->params->get('email');
				$destination = 'zooley@purdue.edu';
				$rname = trans('global.unknown');

				if ($resourceid  = $this->params->get('resourceid'))
				{
					$resource = Asset::find($resourceid);
					$rname = $resource ? $resource->name : $rname;
				}

				$message = new Application($data, $destination, $rname);

				//Mail::to($destination)->send($message);

				if ($this->params->get('send_confirmation'))
				{
					$message = new Confirmation($data, $destination, $rname);
					//echo $message->render();
					//Mail::to($data['email'])->send($message);
				}

				return view($this->getViewName('success'), [
					'data' => $data,
					'errors' => $errors,
					'params' => $this->params,
				]);
			}
		}

		return view($this->getViewName('index'), [
			'data' => $data,
			'errors' => $errors,
			'params' => $this->params,
		]);
	}
}
