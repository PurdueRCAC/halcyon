<?php
namespace App\Widgets\Contactform;

use Illuminate\Support\Facades\Mail;
use App\Modules\Widgets\Entities\Widget;
use App\Widgets\Contactform\Mail\Message;
use App\Widgets\Contactform\Mail\Confirmation;

/**
 * Display a contact form
 */
class Contactform extends Widget
{
	/**
	 * Display
	 *
	 * @return  void
	 */
	public function run()
	{
		$errors = array();
		$data   = array(
			'name'    => auth()->user() ? auth()->user()->name : '',
			'email'   => auth()->user() ? auth()->user()->email : '',
			'subject' => '',
			'body'    => '',
		);

		$view = 'index';

		if (request()->method() == 'POST')
		{
			$data = request()->input('contact', $data);

			if (!isset($data['name']))
			{
				$errors[] = trans('widget.earlyuserform::earlyuserform.error.invalid name');
			}

			if (!isset($data['email']))
			{
				$errors[] = $this->params->get('no_email', trans('widget.earlyuserform::earlyuserform.error.invalid email'));
			}

			if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL))
			{
				$errors[] = $this->params->get('invalid_email', trans('widget.earlyuserform::earlyuserform.error.invalid email'));
			}

			if (empty($errors))
			{
				// Prepare and send actual email
				$dest_email = $this->params->get('recipient_email');
				$dest_email = $dest_email ?: config('mail.from.address');
				$dest_name = $this->params->get('recipient_name');
				$dest_name = $dest_name ?: config('app.name');

				$message = new Message($data, $dest_email, $dest_name);

				Mail::to([$dest_email, $dest_name])->send($message);

				if ($this->params->get('send_confirmation'))
				{
					$message = new Confirmation($data, $dest_email, $dest_name);

					Mail::to([$data['email'], $data['name']])->send($message);
				}

				$view = 'success';
			}
		}

		return view($this->getViewName($view), [
			'data'   => $data,
			'errors' => $errors,
			'widget' => $this->model,
			'params' => $this->params,
		]);
	}
}
