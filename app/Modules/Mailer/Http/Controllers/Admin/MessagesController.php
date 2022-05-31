<?php

namespace App\Modules\Mailer\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Modules\Users\Models\User;
use App\Modules\History\Models\Log;
use App\Modules\Mailer\Models\Message;
use App\Modules\Mailer\Mail\GenericMessage;
use App\Halcyon\Http\StatefulRequest;
use App\Halcyon\Access\Map;
use Carbon\Carbon;

class MessagesController extends Controller
{
	/**
	 * Display a listing of the resource.
	 * 
	 * @param  StatefulRequest $request
	 * @return Response
	 */
	public function index(StatefulRequest $request)
	{
		// Get filters
		$filters = array(
			'search'   => null,
			// Paging
			'limit'    => config('list_limit', 20),
			'page'     => 1,
			// Sorting
			'order'     => 'sent_at',
			'order_dir' => 'desc',
		);

		$reset = false;
		foreach ($filters as $key => $default)
		{
			if ($key != 'page'
			 && $request->has($key)
			 && $request->input($key) != session()->get('users.messages.filter_' . $key))
			{
				$reset = true;
			}
			$filters[$key] = $request->state('users.messages.filter_' . $key, $key, $default);
		}
		$filters['page'] = $reset ? 1 : $filters['page'];

		if (!in_array($filters['order'], ['id', 'subject', 'body', 'state', 'access', 'category_id']))
		{
			$filters['order'] = 'sent_at';
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = 'desc';
		}

		$query = Message::query()
			->where('template', '=', 0);

		if ($filters['search'])
		{
			if (is_numeric($filters['search']))
			{
				$query->where('id', '=', (int)$filters['search']);
			}
			else
			{
				$query->where(function($where) use ($filters)
				{
					$where->where('subject', 'like', '%' . strtolower((string)$filters['search']) . '%')
						->orWhere('body', 'like', '%' . strtolower((string)$filters['search']) . '%');
				});
			}
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'], ['*'], 'page', $filters['page']);

		return view('mailer::admin.messages.index', [
			'rows'    => $rows,
			'filters' => $filters,
		]);
	}

	/**
	 * Remove the specified entry
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function delete(Request $request)
	{
		// Incoming
		$ids = $request->input('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$success = 0;

		foreach ($ids as $id)
		{
			// Delete the entry
			// Message: This is recursive and will also remove all descendents
			$row = Message::findOrFail($id);

			if (!$row->delete())
			{
				$request->session()->flash('error', $row->getError());
				continue;
			}

			$success++;
		}

		if ($success)
		{
			$request->session()->flash('success', trans('global.messages.item deleted', ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Return to default page
	 *
	 * @return  Response
	 */
	public function cancel()
	{
		return redirect(route('admin.mailer.index'));
	}

	/**
	 * Store a newly created entry
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function preview(Request $request, $id)
	{
		$row = Message::findOrFail($id);
		$user = auth()->user();

		$message = new GenericMessage($row, $user);

		echo $message->render();
		exit();
	}

	/**
	 * Show the form for editing the specified entry
	 *
	 * @param   integer  $id
	 * @return  Response
	 */
	public function create(Request $request)
	{
		$id = $request->input('id');

		$row = $id ? Message::findOrFail($id) : new Message;

		if ($fields = app('request')->old())
		{
			$row->fill($fields);
		}

		$templates = Message::query()
			->where('template', '=', 1)
			->orderBy('subject', 'asc')
			->get();

		return view('mailer::admin.messages.edit', [
			'row' => $row,
			'templates' => $templates,
		]);
	}

	/**
	 * Store a newly created entry
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function send(Request $request)
	{
		$rules = [
			'subject' => 'required|string|max:255',
			'body' => 'required|string|max:15000',
			'alert' => 'nullable|string|max:50',
			'fromemail' => 'required|email',
			'fromname' => 'nullable|string|max:150',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors($validator->messages());
		}

		$from = [
			'email' => $request->input('fromemail'),
			'name'  => $request->input('fromname'),
		];
		$from['name'] = $from['name'] ? $from['name'] : $from['email'];

		$cc  = [];

		if ($request->has('cc'))
		{
			$ccs = $request->input('cc');
			$cc = $this->toEmails($ccs, $cc, $request);
		}

		$bcc = [];

		if ($request->has('bcc'))
		{
			$bccs = $request->input('bcc');
			$bcc = $this->toEmails($bccs, $bcc, $request);
		}

		$to = [];
		$users = [];

		if ($request->has('user'))
		{
			$users = $request->input('user');
			$users = explode(',', $users);
			$users = array_map('trim', $users);
		}

		if ($request->has('role'))
		{
			$role = $request->input('role');

			$a = (new User)->getTable();
			$b = (new Map)->getTable();

			$results = User::query()
				->select($a . '.id')
				->leftJoin($b, $b . '.user_id', $a . '.id')
				->whereIn($b . '.role_id', (array)$role)
				->get()
				->pluck('id')
				->toArray();

			$users = $users + $results;
		}

		$users = array_filter($users);
		$users = array_unique($users);

		$success = 0;

		if (count($users) <= 0)
		{
			return redirect()->back()
				->withInput($request->input())
				->withErrors([trans('mailer::mailer.invalid.user list')]);
		}

		$row = new Message;
		$row->subject = $request->input('subject');
		$row->body = $request->input('body');
		if ($request->has('alert'))
		{
			$row->alert = $request->input('alert');
		}
		// We need to save it before sending messsages out so the log
		// has an object ID to point to.
		$row->save();

		foreach ($users as $id)
		{
			if (is_numeric($id))
			{
				$user = User::find($id);
			}
			elseif (filter_var($id, FILTER_VALIDATE_EMAIL))
			{
				$user = User::findByEmail($id);

				if (!$user)
				{
					$user = new User;
					$user->name = $id;
					$user->username = $id;
					$user->email = $id;
				}
			}

			if (!$user || !$user->email)
			{
				$request->session()->flash('warning', trans('mailer::mailer.error.account not found', ['id' => $id]));
				continue;
			}

			$to[] = $user->email;

			$message = new GenericMessage($row, $user);

			Mail::to($user->email)
				->cc($cc)
				->bcc($bcc)
				->from($from['email'], $from['name'])
				->send($message);

			$success++;

			$this->log($user, $row);
		}

		$row->sent_at = Carbon::now();
		$row->sent_by = auth()->user()->id;
		$row->recipients->set('to', $to);
		$row->recipients->set('cc', $cc);
		$row->recipients->set('bcc', $bcc);
		$row->save();

		if ($success)
		{
			$request->session()->flash('success', trans('mailer::mailer.sent message to', ['count' => $success]));
		}

		return $this->cancel();
	}

	/**
	 * Convert a string of user IDs or emails into an array of emails
	 *
	 * @param string $str
	 * @param array  $emails
	 * @param Request $request
	 * @return array
	 */
	protected function toEmails($str, $emails = array(), $request)
	{
		$str = explode(',', $str);
		$str = array_map('trim', $str);
		$str = array_filter($str);
		$str = array_unique($str);

		foreach ($str as $id)
		{
			if (is_numeric($id))
			{
				$user = User::find($id);

				if (!$user)
				{
					$request->session()->flash('warning', trans('mailer::mailer.error.account not found', ['id' => $id]));
					continue;
				}

				$emails[] = $user->email;
			}
			elseif (filter_var($id, FILTER_VALIDATE_EMAIL))
			{
				$emails[] = $id;
			}
			else
			{
				$request->session()->flash('warning', trans('mailer::mailer.invalid.recipient', ['id' => $id]));
			}
		}

		$emails = array_filter($emails);
		$emails = array_unique($emails);

		return $emails;
	}

	/**
	 * Log email
	 *
	 * @param   object $user
	 * @param   object $message
	 * @return  void
	 */
	protected function log($user, $message)
	{
		Log::create([
			'ip'              => request()->ip(),
			'userid'          => (auth()->user() ? auth()->user()->id : 0),
			'status'          => 200,
			'transportmethod' => 'POST',
			'servername'      => request()->getHttpHost(),
			'uri'             => Str::limit($user->email, 128, ''),
			'app'             => 'email',
			'objectid'        => (int)$message->id,
			'payload'         => $message->subject,
			'classname'       => Str::limit('MessagesController', 32, ''),
			'classmethod'     => Str::limit('send', 16, ''),
			'targetuserid'    => (int)$user->id,
		]);
	}

	/**
	 * Store a newly created entry
	 *
	 * @param   Request  $request
	 * @return  Response
	 */
	public function show(Request $request, $id)
	{
		$row = Message::findOrFail($id);

		return view('mailer::admin.messages.show', [
			'row' => $row,
		]);
	}
}
