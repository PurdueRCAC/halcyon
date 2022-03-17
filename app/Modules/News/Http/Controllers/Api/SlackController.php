<?php

namespace App\Modules\News\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Validator;
use App\Modules\News\Models\Association;
use App\Modules\News\Models\Article;
use App\Modules\Users\Models\User;
use GuzzleHttp\Client;
use Carbon\Carbon;

/**
 * Slack interactions
 *
 * @apiUri    /news/slack
 */
class SlackController extends Controller
{
	/**
	 * Proccess Slack interactions
	 *
	 * @apiMethod POST
	 * @apiUri    /news/slack
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "tagresources",
	 * 		"description":   "Filter by types that allow articles to tag resources",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "location",
	 * 		"description":   "Filter by types that allow articles to set location",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "future",
	 * 		"description":   "Filter by types that allow articles to set future",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "ongoing",
	 * 		"description":   "Filter by types that allow articles to set ongoing",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "search",
	 * 		"description":   "A word or phrase to search for.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string"
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "limit",
	 * 		"description":   "Number of result per page.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   20
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "page",
	 * 		"description":   "Number of where to start returning results.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "integer",
	 * 			"default":   1
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order",
	 * 		"description":   "Field to sort results by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "datetimecreated",
	 * 			"enum": [
	 * 				"id",
	 * 				"motd",
	 * 				"datetimecreated",
	 * 				"datetimeremoved"
	 * 			]
	 * 		}
	 * }
	 * @apiParameter {
	 * 		"in":            "query",
	 * 		"name":          "order_dir",
	 * 		"description":   "Direction to sort results by.",
	 * 		"required":      false,
	 * 		"schema": {
	 * 			"type":      "string",
	 * 			"default":   "desc",
	 * 			"enum": [
	 * 				"asc",
	 * 				"desc"
	 * 			]
	 * 		}
	 * }
	 * @apiResponse {
	 * 		"200": {
	 * 			"description": "Successful entries lookup",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"message": "Text"
	 * 					}
	 * 				}
	 * 			}
	 * 		},
	 * 		"415": {
	 * 			"description": "Invalid data"
	 * 		}
	 * }
	 * @param  Request  $request
	 * @return Response
	 */
	public function index(Request $request)
	{
		/*
		{
			"type": "block_actions",
			"user": {
				"id": "U5WM65ET1",
				"username": "someone",
				"name": "someone",
				"team_id": "T0NVANZP0"
			},
			"api_app_id": "A02",
			"token": "Shh_its_a_seekrit",
			"container": {
				"type": "message",
				"text": "The contents of the original message where the action originated"
			},
			"trigger_id": "12466734323.1395872398",
			"team": {
				"id": "T0NVANZP0",
				"domain": "purduercac"
			},
			"enterprise": null,
			"is_enterprise_install": false,
			"state": {
				"values": {}
			},
			"response_url": "https://www.postresponsestome.com/T123567/1509734234",
			"actions": [
				{
					"type": "button",
					"block_id": "BWkx",
					"action_id": "actionId-0",
					"text": {
						"type": "plain_text",
						"text": "Claim",
						"emoji": true
					},
					"value": "reserve_15",
					"action_ts": "1647464263.215355"
				}
			]
		}
		*/
		$message = 'Acknowledged';

		if ($request->has('payload'))
		{
			$payload = $request->input('payload');

			if (is_string($payload))
			{
				$payload = json_decode($payload, true);
			}

			foreach ($payload['actions'] as $action)
			{
				$parts = explode('_', $action['action_id']);

				$type = $parts[0];
				$article_id = $parts[1];

				$article = Article::findOrFail($article_id);
if ($payload['user']['username'] == 'zooley')
{
        $payload['user']['username'] = 'rices';
}
				$user = User::findByUsername($payload['user']['username']);

				if (!$user || !$user->id)
				{
					return response()->json(['message' => 'Unknown user'], 415);
				}

				if ($type == 'reserve')
				{
					$row = new Association;
					$row->newsid = $article->id;
					$row->assoctype = 'staff';
					$row->associd = $user->id;
					$row->comment = 'Reserved via Slack bot';

					if (!$row->save())
					{
						return response()->json(['message' => trans('global.messages.creation failed')], 500);
					}

					$message = $payload['message'];

					foreach ($message['attachments'] as $j => $attach)
					{
						foreach ($attach['blocks'] as $i => $block)
						{
							if ($block['type'] == 'actions')
							{
								$message['replace_original'] = true;

								$block['type'] = 'context';
								$block['elements'] = [
									[
										'type' => 'mrkdwn',
										'text' => ':white_check_mark: *@' . $user->username . '* claimed this.'
									]
								];

								$message['attachments'][$j]['blocks'][$i] = $block;
							}
						}
					}

					if (isset($payload['response_url']))
					{
						$client = new Client();
						$res = $client->request('POST', $payload['response_url'], [
							'json' => $message
						]);

						if ($res->getStatusCode() >= 400)
						{
							return response()->json(['message' => 'Failed to response'], 500);
						}
					}

					$message = 'Reserved by @' . $user->username;
				}
				elseif ($type == 'launch')
				{
					/*foreach ($article->associations as $assoc)
					{
						if ($assoc->userid == $user->id)
						{
							$assoc->datetimeattended = Carbon::now();
							$assoc->save();

							//return redirect($article->url);
							$message = 'Launched by @' . $user->username;
							break;
						}
					}*/
					$message = 'Launched by @' . $user->username;
				}
			}
		}

		return response()->json([
				'text' => $message,
			], 200);
	}
}
