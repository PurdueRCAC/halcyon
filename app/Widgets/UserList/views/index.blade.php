<?php
/**
 * User list
 */
?>
<div class="users">
	@if (count($users))
		<table>
			<caption>{{ trans('widget.userlist::userlist.staff directory') }}</caption>
			<thead>
				<tr>
					<th scope="col">{{ trans('widget.userlist::userlist.staff') }}</th>
					@if ($params->get('show_email', 1))
						<th scope="col">{{ trans('widget.userlist::userlist.email') }}</th>
					@endif
					@if ($params->get('show_phone'))
						<th scope="col">{{ trans('widget.userlist::userlist.phone') }}</th>
					@endif
					@if ($params->get('show_specialty'))
						<th scope="col">{{ trans('widget.userlist::userlist.specialty') }}</th>
					@endif
				</tr>
			</thead>
			<tbody>
			@foreach ($users as $user)
				<tr>
					<td>{{ $user->name }}</td>
					@if ($params->get('show_email', 1))
						<td>{{ $user->email }}</td>
					@endif
					@if ($params->get('show_phone'))
						<td>{{ $user->email }}</td>
					@endif
					@if ($params->get('show_specialty'))
						<td>{{ $user->specialty }}</td>
					@endif
				</tr>
			@endforeach
			</tbody>
		</table>
	@else
		<p>{{ trans('widget.news::news.no articles found') }}</p>
	@endif
</div>
