<?php
namespace App\Widgets\Helpform;

use Illuminate\Support\Facades\Mail;
use App\Widgets\Helpform\Mail\Ticket;
use App\Modules\Widgets\Entities\Widget;
use App\Modules\Resources\Models\Asset;
use App\Modules\Knowledge\Models\Associations;
use App\Modules\Users\Models\User;

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
		$request = request();

		if ($request->method() == 'POST')
		{
			$data = array(
				'email'    => $request->input('email'),
				'subject'  => $request->input('subject'),
				'resource' => $request->input('resource', []),
				'report'   => $request->input('report'),
				'user'     => null,
			);

			$errors = array();

			if (!isset($data['email']))
			{
				$errors[] = trans('widget.helpform::helpform.error.email');
			}

			if (!isset($data['report']))
			{
				$errors[] = trans('widget.helpform::helpform.error.report');
			}

			if (!isset($data['subject']))
			{
				$errors[] = trans('widget.helpform::helpform.error.subject');
			}

			if (!validate_captcha('helpcaptcha'))
			{
				$errors[] = trans('widget.helpform::helpform.error.captcha');
			}

			// Prepare and send actual email
			$destination = $this->params->get('email');

			if (!$destination)
			{
				$errors[] = trans('widget.helpform::helpform.error.misconfigured');
			}

			if (empty($errors))
			{
				// Collect selected resource names
				$res = $data['resource'];
				if (is_string($res))
				{
					$res = explode(',', $res);
				}
				$resources = array();
				foreach ($res as $resourceid)
				{
					$resource = Asset::find($resourceid);
					if ($resource)
					{
						$resources[] = $resource->name;
					}
				}
				$data['resources'] = implode(', ', $resources);

				// Do they have an account?
				if (strstr($data['email'], '@') == '@purdue.edu')
				{
					$user = User::findByEmail($data['email']);
					$data['user'] = $user;
				}

				// Build the message
				$message = new Ticket($data, $destination);

				$files = $request->file('upload');
				if ($files)
				{
					foreach ($files as $file)
					{
						$message->attach($file->getRealPath(), [
							'as'   => $file->getClientOriginalName(),
							'mime' => $file->getMimeType(),
						]);
					}
				}

				//echo '<pre>';
				//echo $message->render();
				//echo '</pre>';
				Mail::to($destination)->send($message);

				return view($this->getViewName('success'), [
					'data'   => $data,
					'errors' => $errors,
					'params' => $this->params,
				]);
			}
		}

		$topics = $this->params->get('topic', []);

		foreach ($topics as $i => $topic)
		{
			$topics[$i]['name'] = preg_replace('/[^a-zA-Z0-9]+/', '', strtolower($topics[$i]['title']));
			$topics[$i]['content'] = '';
			$page = Associations::find($topics[$i]['article']);

			if ($page)
			{
				$topics[$i]['content'] = $page->page->body;
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
			'types'  => $types,
			'errors' => $errors,
			'topics' => $topics,
		]);
	}
}
