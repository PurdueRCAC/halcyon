<?php

namespace App\Modules\Finder\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Modules\Finder\Models\Service;
use App\Modules\Finder\Models\Facet;

/**
 * Finder
 *
 * @apiUri    /api/finder
 */
class FinderController extends Controller
{
	/**
	 * Get settings
	 *
	 * @apiMethod GET
	 * @apiUri    /api/finder/settings
	 * @return  Response
	 */
	public function settings()
	{
		$data = config('module.finder', []);

		if (empty($data))
		{
			$data = include dirname(dirname(dirname(__DIR__))) . '/Config/config.php';
		}

		return new JsonResource($data);
	}

	/**
	 * Get service list
	 *
	 * @apiMethod GET
	 * @apiUri    /api/finder/servicelist
	 * @return  Response
	 */
	public function servicelist()
	{
		$services = Service::servicelist();

		return new JsonResource($services);
	}

	/**
	 * Get facet tree
	 *
	 * @apiMethod GET
	 * @apiUri    /api/finder/facettree
	 * @return  Response
	 */
	public function facettree()
	{
		$questions = Facet::tree();//$this->createFacetTree();

		return new JsonResource($questions);
	}

	/**
	 * Send an email
	 *
	 * @apiMethod POST
	 * @apiUri    /api/finder/sendmail
	 * @apiAuthorization  true
	 * @param   Request $request
	 * @return  Response
	 */
	public function sendmail(Request $request)
	{
		$qdata = $request->input("qdata");
		$sdata = $request->input("sdata");

		$body = "Thank you for using the Finder tool. " .
			"We hope it was useful.\r\n\r\n" .
			"Your selected criteria were:\r\n";

		$questions = TermData::tree();//$this->createFacetTree();

		$facets = [];

		foreach ($qdata as $qitem)
		{
			$question_id = $qitem[0];
			$facet_id    = $qitem[1];

			$facets[] = $facet_id;

			foreach ($questions as $question)
			{
				if ($question['id'] == $question_id)
				{
					$body .= '* ' . $question['name'] . ' -- ';
					foreach ($question['choices'] as $choice)
					{
						if ($choice['id'] == $facet_id)
						{
							$body .= $choice['name'] . "\r\n";
						}
					}
				}
			}
		}

		$body = $body . "\r\nYour resulting choices were:\r\n";

		$services = Node::services();//$this->createTestServiceList();

		foreach ($sdata as $svc)
		{
			foreach ($services as $service)
			{
				if ($service['id'] == $svc)
				{
					$body .= '* '  . $service['title'] . "\r\n";
				}
			}
		}

		$body .= "\r\nUse this link to return to the tool ".
				"with your criteria already selected: " .
				$request->getSchemeAndHttpHost() .
				"/finder?facets=" .
				implode($facets, ',') .
				"\r\n\r\n" .
				"If you have any further questions or need more information about " .
				"Finder services, please contact the helpdesk to set up a consultation, ".
				"or contact the service owners " .
				"directly (contact details in tool comparison table).\r\n\r\n";

		$subject = 'Assistance request from Finder application';

		$mailManager = \Drupal::service('plugin.manager.mail');
		$module = 'finder';
		$key = 'complete_form';

		$to = $request->input('email');
		$params['message'] = $body;
		$params['subject'] = $subject;

		error_log("to is $to");
		error_log("message is {$params['message']}");

		$langcode = \Drupal::currentUser()->getPreferredLangcode();
		$send = true;
		$result = $mailManager->mail($module, $key, $to, $langcode, $params, NULL, $send);

		if ($result['result'] !== true)
		{
			return response()->json(trans('finder::finder.There was a problem sending your message and it was not sent.'), 500);
		}

		return response()->json(trans('finder::finder.Your message has been sent.'), 200);
	}
}
