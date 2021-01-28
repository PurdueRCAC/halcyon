<?php
namespace App\Widgets\Helpform;

use App\Modules\Widgets\Entities\Widget;
use App\Widgets\Helpform\Mail\Ticket;
use App\Modules\Resources\Entities\Asset;

/**
 * Display a help form
 */
class Helpform extends Widget
{
	/**
	 * Display
	 *
	 * @return  void
	 */
	public function run()
	{
		if (request()->method() == 'POST')
		{
			$data = array(
				'email' => request()->input('email'),
				'subject' => request()->input('subject'),
				'resource' => request()->input('resource', [])
				'report' => request()->input('report'),
			);

			if (!isset($data['name']))
			{
				$errors[] = trans('Please provide a name.');
			}

			if (!isset($data['email']))
			{
				$errors[] = trans('Please provide a valid email.');
			}

			if (!isset($data['report']))
			{
				$errors[] = trans('Please provide a report.');
			}

			if (!isset($data['subject']))
			{
				$errors[] = trans('Please provide a subject.');
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

				$message = new Ticket($data, $destination;

				//Mail::to($destination)->send($message);

				return view($this->getViewName('success'), [
					'data' => $data,
					'errors' => $errors,
					'params' => $this->params,
				]);
			}
		}

		$resources = Asset::query()
			->withTrashed()
			->whereIsActive()
			->where('listname', '!=', '')
			->orderBy('name', 'asc')
			->get();

		$types = array();
		foreach ($resources as $resource)
		{
			$tname = 'Services';
			if ($resource->type)
			{
				$tname = $resource->type->name;
			}

			if (!isset($types[$tname]))
			{
				$types[$tname] = array();
			}
			$types[$tname][] = $resource;
		}
		ksort($types);

		$layout = $this->params->get('layout');
		$layout = $layout ?: 'index';

		return view($this->getViewName($layout), [
			'types' => $types,
		]);
	}
}
