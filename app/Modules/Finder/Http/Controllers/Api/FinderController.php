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
	 * @apiResponse {
	 * 		"200": {
	 * 			"description": "Successful entry modification",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": {
	 * 						"name": "Finder",
	 * 						"title": "Data Storage Solutions Finder",
	 * 						"subtitle": "University researchers, staff, and students have a variety of options to store and collaborate with their Purdue data. This tool will offer recommendations of Purdue solutions appropriate to your usage needs and the data security constraints.",
	 * 						"question_header": "Answer these questions to help identify storage solutions and services that are most suitable for your needs.",
	 * 						"service_header": "Select data storage solutions you would like to compare in details.",
	 * 						"chart_header": "Select data storage solutions you would like to compare.",
	 * 						"email_form_header": "This is the email email_form",
	 * 						"email_address": "help@example.org",
	 * 						"email_name": "Example Org",
	 * 						"main_header": "Main Header",
	 * 						"button_select_all": "Select All",
	 * 						"button_clear_selections": "Clear Selections"
	 * 					}
	 * 				}
	 * 			}
	 * 		}
	 * }
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
	 * @apiResponse {
	 * 		"200": {
	 * 			"description": "Successful entry modification",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": [
	 * 						{
	 * 							"id": 14,
	 * 							"title": "Amazon Drive",
	 * 							"summary": "A personal cloud storage platform hosted by Amazon.",
	 * 							"facet_matches": [
	 * 								4,
	 * 								7,
	 * 								10,
	 * 								13,
	 * 								15
	 * 							],
	 * 							"field_data": {
	 * 								"field_also_known_as": {
	 * 									"value": "<p><span>Amazon Clouddrive, Amazon Photos</span></p>\r\n",
	 * 									"label": "Also Known As",
	 * 									"weight": 12
	 * 								},
	 * 								"field_capacity_details": {
	 * 									"value": "<p><span>Free basic tier (5 GB), several paid plans</span>.</p>\r\n",
	 * 									"label": "Capacity Details",
	 * 									"weight": 1
	 * 								},
	 * 								"field_cost": {
	 * 									"value": "<p><span>Free (5 GB), paid.</span></p>\r\n",
	 * 									"label": "Cost",
	 * 									"weight": 3
	 * 								}
	 * 							}
	 * 						},
	 * 						{
	 * 							"id": 8,
	 * 							"title": "Box Research Lab Folder",
	 * 							"summary": "Box Research Lab Folder",
	 * 							"facet_matches": [
	 * 								3,
	 * 								4,
	 * 								7,
	 * 								10,
	 * 								13,
	 * 								15,
	 * 								16
	 * 							],
	 * 							"field_data": {
	 * 								"field_also_known_as": {
	 * 									"value": "<p>Box, Box.com</p>\r\n",
	 * 									"label": "Also Known As",
	 * 									"weight": 12
	 * 								},
	 * 								"field_capacity_details": {
	 * 									"value": "<p>Box provides unlimited storage space, however individual files may not exceed 15 GB.</p>\r\n",
	 * 									"label": "Capacity Details",
	 * 									"weight": 1
	 * 								},
	 * 								"field_cost": {
	 * 									"value": "<p>Free</p>\r\n",
	 * 									"label": "Cost",
	 * 									"weight": 3
	 * 								}
	 * 							}
	 * 						}
	 * 					]
	 * 				}
	 * 			}
	 * 		}
	 * }
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
	 * @apiResponse {
	 * 		"200": {
	 * 			"description": "Successful entry modification",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": [
	 * 					{
	 * 						"id": 11,
	 * 						"name": "Is your dataset restricted to U.S. persons only?",
	 * 						"control_type": "radio",
	 * 						"parent": 0,
	 * 						"weight": 1,
	 * 						"selected": false,
	 * 						"description": "<p>Export controlled data (ITAR/EAR) are restricted by law to U.S. persons only. This means that non-U.S. persons including international students or individuals from foreign countries are barred from accessing this data.</p>\r\n\r\n<p>Contact <a href=\"https://www.purdue.edu/research/regulatory-affairs/\";>Purdue Regulatory Affairs</a> for more information.</p>\r\n",
	 * 						"choices": [
	 * 							{
	 * 								"id": 12,
	 * 								"name": "Yes",
	 * 								"control_type": null,
	 * 								"parent": 11,
	 * 								"weight": 0,
	 * 								"description": null
	 * 							},
	 * 							{
	 * 								"id": 13,
	 * 								"name": "No",
	 * 								"control_type": null,
	 * 								"parent": 11,
	 * 								"weight": 1,
	 * 								"description": null
	 * 							}
	 * 						]
	 * 					},
	 * 					{
	 * 						"id": 8,
	 * 						"name": "Will your project utilize a community cluster?",
	 * 						"control_type": "radio",
	 * 						"parent": 0,
	 * 						"weight": 2,
	 * 						"selected": false,
	 * 						"description": "<p><a href=\"https://www.rcac.purdue.edu/services/communityclusters/\";>Community clusters at Purdue</a> provide cost effective computing resources for researchers to run tasks requiring large amounts of computing resources. These resources are hosted by Purdue and are managed by Purdue Research Computing.</p>\r\n\r\n<p>For more information on community clusters, <a href=\"mailto:rcac-help@purdue.edu\">contact Purdue Research Computing</a>.</p>\r\n",
	 * 						"choices": [
	 * 							{
	 * 								"id": 9,
	 * 								"name": "Yes",
 	 * 								"control_type": null,
	 * 								"parent": 8,
	 * 								"weight": 0,
	 * 								"description": null
	 * 							},
	 * 							{
	 * 								"id": 10,
	 * 								"name": "No",
	 * 								"control_type": null,
	 * 								"parent": 8,
	 * 								"weight": 1,
	 * 								"description": null
	 * 							}
	 * 						]
	 * 					}
	 * 					]
	 * 				}
	 * 			}
	 * 		}
	 * }
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
	 * @apiResponse {
	 * 		"200": {
	 * 			"description": "Successful entry creation",
	 * 			"content": {
	 * 				"application/json": {
	 * 					"example": "Your message has been sent."
	 * 				}
	 * 			}
	 * 		}
	 * }
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
