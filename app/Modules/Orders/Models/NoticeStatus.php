<?php
namespace App\Modules\Orders\Models;

/**
 * Order notice status
 */
class NoticeStatus
{
	/**
	 * Order notice states
	 *
	 * @var int
	 */
	const PENDING_PAYMENT = 1;
	const PENDING_BOASSIGNMENT = 2;
	const PENDING_APPROVAL = 3;
	const PENDING_FULFILLMENT = 4;
	const PENDING_COLLECTION = 6;
	const COMPLETE = 7;
	const CANCELED = -1;
	const NO_NOTICE = 0;
	const CANCELED_NOTICE = 8;
	const ACCOUNT_ASSIGNED = 3;
	const ACCOUNT_APPROVED = 4;
	const ACCOUNT_DENIED = 5;
}
