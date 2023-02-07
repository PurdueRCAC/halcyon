<?php

namespace App\Modules\Mailer\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Modules\Mailer\Models\Message;
use App\Modules\Mailer\Mail\GenericMessage;
use App\Modules\History\Models\Log;
use App\Modules\Users\Models\User;
use App\Modules\Groups\Models\Member;
use App\Halcyon\Access\Map;
use Carbon\Carbon;

/**
 * Mail messages
 *
 * @apiUri    /mail
 */
class MessagesController extends Controller
{
	/**
	 * Display a listing of entries
	 *
	 * @apiMethod GET
	 * @apiUri    /mail
	 * @apiParameter {
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "limit",
	 * 		"description":   "Number of result per page.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   20
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "page",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   1
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "sent_at",
	 * 			"enum": [
	 * 				"id",
	 * 				"subject",
	 * 				"created_at",
	 * 				"updated_at",
	 * 				"sent_at",
	 * 				"sent_by",
	 * 				"template",
	 * 				"alert"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "asc",
	 * 			"enum": [
	 * 				"asc",
	 * 				"desc"
	 * 			]
	 * 		}
	 * }
	 * @param  Request  $request
	 * @return ResourceCollection
	 */
	public function index(Request $request)
	{
		// Get filters
		$filters = array(
			'search'   => null,
			'template' => 0,
			// Paging
			'limit'    => config('list_limit', 20),
			// Sorting
			'order'     => Message::$orderBy,
			'order_dir' => Message::$orderDir,
		);

		foreach ($filters as $key => $default)
		{
			$filters[$key] = $request->input($key, $default);
		}

		if (!in_array($filters['order'], ['id', 'subject', 'created_at', 'updated_at', 'sent_at', 'sent_by', 'alert', 'template']))
		{
			$filters['order'] = Message::$orderBy;
		}

		if (!in_array($filters['order_dir'], ['asc', 'desc']))
		{
			$filters['order_dir'] = Message::$orderDir;
		}

		// Get records
		$query = Message::query();

		if ($filters['search'])
		{
			$query->where(function($where) use ($filters)
			{
				$where->where('subject', 'like', '%' . $filters['search'] . '%')
					->orWhere('body', 'like', '%' . $filters['search'] . '%');
			});
		}

		$rows = $query
			->orderBy($filters['order'], $filters['order_dir'])
			->paginate($filters['limit'])
			->appends(array_filter($filters))
			->each(function($item, $key)
			{
				$item->api = route('api.mailer.read', ['id' => $item->id]);
			});

		return new ResourceCollection($rows);
	}

	/**
	 * Create a new entry
	 *
	 * @apiMethod POST
	 * @apiUri    /mail
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "subject",
	 * 		"description":   "Message subject",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 255
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "body",
	 * 		"description":   "Message contents, formatted using MarkDown",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "template",
	 * 		"description":   "If the message is a template or not",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "alert",
	 * 		"description":   "An alert level to use for styling HTML portions of emails",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   null,
	 * 			"enum": [
	 * 				"info",
	 * 				"warning",
	 * 				"danger"
	 * 			]
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"201": {
	 * 			"description": "Successful entry creation",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 2,
	 * 						"subject": "Scratch Filesystem Purge Policy Violation",
	 * 						"body": "Don't violate our policies!",
	 * 						"template": 0,
	 * 						"alert": "info",
	 * 						"created_at": "2022-05-24 12:31:01",
	 * 						"updated_at": null,
	 * 						"deleted_at": null,
	 * 						"sent_at": "2022-05-24 12:31:01",
	 * 						"sent_by": 1234,
	 * 						"api": "https://example.org/api/mail/2"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param  Request $request
	 * @return JsonResource|JsonResponse
	 */
	public function create(Request $request)
	{
		$rules = [
			'subject' => 'required|string|max:255',
			'body' => 'required|string|max:15000',
			'template' => 'nullable|integer',
			'alert' => 'nullable|string|max:50',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = new Message();
		$row->subject = $request->input('subject');
		$row->body = $request->input('body');
		$row->template = $request->input('template', 0);
		if ($request->has('alert'))
		{
			$row->alert = $request->input('alert');
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.save failed')], 500);
		}

		$row->api = route('api.mailer.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Retrieve an entry
	 *
	 * @apiMethod GET
	 * @apiUri    /mail/{id}
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"200": {
	 * 			"description": "Successful entry read",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 2,
	 * 						"subject": "Scratch Filesystem Purge Policy Violation",
	 * 						"body": "Don't violate our policies!",
	 * 						"template": 0,
	 * 						"alert": "info",
	 * 						"created_at": "2022-05-24 12:31:01",
	 * 						"updated_at": null,
	 * 						"deleted_at": null,
	 * 						"sent_at": "2022-05-24 12:31:01",
	 * 						"sent_by": 1234,
	 * 						"api": "https://example.org/api/mail/2"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param  int  $id
	 * @return JsonResource
	 */
	public function read(int $id)
	{
		$row = Message::findOrFail((int)$id);

		$row->api = route('api.mailer.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Update an entry
	 *
	 * @apiMethod PUT
	 * @apiUri    /mail/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "subject",
	 * 		"description":   "Message subject",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"maxLength": 255
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "body",
	 * 		"description":   "Message contents, formatted using MarkDown",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "template",
	 * 		"description":   "If the message is a template or not",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   0
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "alert",
	 * 		"description":   "An alert level to use for styling HTML portions of emails",
	 * 		"type":          "integer",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   null,
	 * 			"enum": [
	 * 				"info",
	 * 				"warning",
	 * 				"danger"
	 * 			]
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"202": {
	 * 			"description": "Successful entry modification",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 2,
	 * 						"subject": "Scratch Filesystem Purge Policy Violation",
	 * 						"body": "Don't violate our policies!",
	 * 						"template": 0,
	 * 						"alert": "info",
	 * 						"created_at": "2022-05-24 12:31:01",
	 * 						"updated_at": null,
	 * 						"deleted_at": null,
	 * 						"sent_at": "2022-05-24 12:31:01",
	 * 						"sent_by": 1234,
	 * 						"api": "https://example.org/api/mail/2"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		},
	 * 		"409": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param   Request $request
	 * @param   int $id
	 * @return  JsonResource|JsonResponse
	 */
	public function update(Request $request, int $id)
	{
		$rules = [
			'subject' => 'nullable|string|max:255',
			'body' => 'nullable|string|max:15000',
			'template' => 'nullable|integer',
			'alert' => 'nullable|string|max:50',
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = Message::findOrFail($id);
		foreach ($rules as $key => $rule)
		{
			if ($request->has($key))
			{
				$row->{$key} = $request->input($key);
			}
		}

		if (!$row->save())
		{
			return response()->json(['message' => trans('global.messages.save failed')], 500);
		}

		$row->api = route('api.mailer.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Delete an entry
	 *
	 * @apiMethod DELETE
	 * @apiUri    /mail/{id}
	 * @apiAuthorization  true
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"204": {
	 * 			"description": "Successful entry deletion"
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param   int  $id
	 * @return  JsonResponse
	 */
	public function delete(int $id)
	{
		$row = Message::findOrFail($id);

		if (!$row->delete())
		{
			return response()->json(['message' => trans('global.messages.delete failed', ['id' => $id])], 500);
		}

		return response()->json(null, 204);
	}

	/**
	 * Post a message
	 *
	 * @apiMethod POST
	 * @apiUri    /mail/send
	 * @apiParameter {
	 * 		"in":            "path",
	 * 		"name":          "id",
	 * 		"description":   "Entry identifier",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "subject",
	 * 		"description":   "Message subject",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "body",
	 * 		"description":   "Message body",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "to",
	 * 		"description":   "A comma-separated list of user IDs or email addresses",
	 * 		"required":      true,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "group",
	 * 		"description":   "A comma-separated list of group IDs to email all users in those gorups",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "fromemail",
	 * 		"description":   "An email address the message is from. Defaults to global site setting.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "fromname",
	 * 		"description":   "A name the message is from. Defaults to global site setting.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "cc",
	 * 		"description":   "A comma-separated list of user IDs or email addresses",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "body",
	 * 		"name":          "bcc",
	 * 		"description":   "A comma-separated list of user IDs or email addresses",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"200": {
	 * 			"description": "Successful entry read",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"id": 2,
	 * 						"subject": "Scratch Filesystem Purge Policy Violation",
	 * 						"body": "Don't violate our policies!",
	 * 						"template": 0,
	 * 						"alert": "info",
	 * 						"created_at": "2022-05-24 12:31:01",
	 * 						"updated_at": null,
	 * 						"deleted_at": null,
	 * 						"sent_at": "2022-05-24 12:31:01",
	 * 						"sent_by": 1234,
	 * 						"api": "https://example.org/api/mail/2"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"404": {
	 * 			"description": "Record not found"
	 * 		}
	 * }
	 * @param  Request $request
	 * @return JsonResource
	 */
	public function send(Request $request)
	{
		$rules = [
			'subject' => 'required|string|max:255',
			'body' => 'required|string|max:15000'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails())
		{
			return response()->json(['message' => $validator->messages()], 415);
		}

		$row = new Message;
		$row->subject = $request->input('subject');
		$row->body = $request->input('body');
		$row->save();

		$from = [
			'email' => $request->input('fromemail', config('mail.from.address')),
			'name'  => $request->input('fromname', config('mail.from.name')),
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

		if ($request->has('group'))
		{
			$groups = $request->input('group');
			$groups = explode(',', $groups);
			$groups = array_map('trim', $groups);

			$results = Member::query()
				->select('userid')
				->whereIn('groupid', (array)$groups)
				->where('membertype', '!=', 4)
				->get()
				->pluck('userid')
				->toArray();

			$users = $users + $results;
		}

		$users = array_filter($users);
		$users = array_unique($users);

		if (count($users) > 0)
		{
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
					return response()->json(['message' => 'Could not find account for user ID #' . $id], 415);
				}

				$to[] = $user->email;

				$message = new GenericMessage($row, $user, $from);

				Mail::to($user->email)
					->cc($cc)
					->bcc($bcc)
					->send($message);

				$this->log($user, $row);
			}
		}

		$row->sent_at = Carbon::now();
		$row->sent_by = auth()->user()->id;
		$row->recipients->set('to', $to);
		$row->recipients->set('cc', $cc);
		$row->recipients->set('bcc', $bcc);
		$row->save();

		$row->api = route('api.mailer.read', ['id' => $row->id]);

		return new JsonResource($row);
	}

	/**
	 * Convert a string of user IDs or emails into an array of emails
	 *
	 * @param string $str
	 * @param array  $emails
	 * @param Request $request
	 * @return array
	 */
	protected function toEmails($str, $emails, $request)
	{
		$str = explode(',', $str);
		$str = array_map('trim', $str);

		foreach ($str as $id)
		{
			if (is_numeric($id))
			{
				$user = User::find($id);

				if (!$user)
				{
					continue;
				}

				$emails[] = $user->email;
			}
			elseif (filter_var($id, FILTER_VALIDATE_EMAIL))
			{
				$emails[] = $id;
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
			'uri'             => $user->email,
			'app'             => 'email',
			'objectid'        => (int)$message->id,
			'payload'         => $message->subject,
			'classname'       => 'MessagesController',
			'classmethod'     => 'send',
			'targetuserid'    => (int)$user->id,
		]);
	}
}
