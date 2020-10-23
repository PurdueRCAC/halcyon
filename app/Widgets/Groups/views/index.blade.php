<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */
?>
<div class="card widget {{ $widget->widget }}" id="{{ $widget->widget . $widget->id }}">

		<table class="table table-hover">
			<caption>{{ trans('widget.groups::groups.recent') }}</caption>
			<thead>
				<tr>
					<th scope="col">{{ trans('widget.groups::groups.name') }}</th>
					<th scope="col">{{ trans('widget.groups::groups.members') }}</th>
					<th scope="col">{{ trans('widget.groups::groups.created') }}</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($groups as $group)
				<tr>
					<td>
						<a href="{{ route('admin.groups.edit', ['id' => $group->id]) }}">
							{{ $group->name }}
						</a>
					</td>
					<td>
						<a href="{{ route('admin.groups.members', ['group' => $group->id]) }}">
							{{ $group->members_count }}
						</a>
					</td>
					<td>
						{{ $group->datetimecreated }}
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>

		<p class="text-center">
			<a class="btn btn-secondary" href="{{ route('admin.groups.index') }}">{{ trans('widget.groups::groups.view all') }}</a>
		</p>
</div>
