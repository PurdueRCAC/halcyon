<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */
?>
<div class="users">
	<?php if (count($users)): ?>
		<table>
			<caption>{{ trans('widget.userlist::userlist.staff directory') }}</caption>
			<thead>
				<tr>
					<th scope="col">{{ trans('widget.userlist::userlist.staff') }}</th>
					<?php if ($params->get('show_email', 1)) { ?>
						<th scope="col">{{ trans('widget.userlist::userlist.email') }}</th>
					<?php } ?>
					<?php if ($params->get('show_phone')) { ?>
						<th scope="col">{{ trans('widget.userlist::userlist.phone') }}</th>
					<?php } ?>
					<?php if ($params->get('show_specialty')) { ?>
						<th scope="col">{{ trans('widget.userlist::userlist.specialty') }}</th>
					<?php } ?>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($users as $user): ?>
				<tr>
					<td>{{ $user->name }}</a>
					<?php if ($params->get('show_email', 1)) { ?>
						<td>{{ $user->email }}</a>
					<?php } ?>
					<?php if ($params->get('show_phone')) { ?>
						<td>{{ $user->email }}</a>
					<?php } ?>
					<?php if ($params->get('show_specialty')) { ?>
						<td>{{ $user->specialty }}</a>
					<?php } ?>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php else: ?>
		<p>{{ trans('widget.news::news.no articles found') }}</p>
	<?php endif; ?>
</div>
