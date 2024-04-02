<?php
/**
 * @package  Groups widget
 */
?>
<div class="card widget {{ $widget->widget }}" id="{{ $widget->widget . $widget->id }}">
	<div class="card-body">
		@if ($widget->showtitle)
			<div class="row">
				<div class="col-md-8">
					<h4 class="card-title py-0">{{ $widget->title }}</h4>
				</div>
				<div class="col-md-4 text-right">
					<a href="{{ route('admin.groups.index') }}">{{ trans('widget.groups::groups.view all') }}</a>
				</div>
			</div>
		@endif

		<table class="table table-hover">
			<caption class="sr-only visually-hidden">{{ trans('widget.groups::groups.recent') }}</caption>
			<thead>
				<tr>
					<th scope="col">{{ trans('widget.groups::groups.name') }}</th>
					<th scope="col" class="text-right">{{ trans('widget.groups::groups.members') }}</th>
					<th scope="col" class="text-center">{{ trans('widget.groups::groups.created') }}</th>
				</tr>
			</thead>
			<tbody>
				@php
				$now = Carbon\Carbon::now()->modify('-1 week');
				@endphp
				@foreach ($groups as $group)
				<tr>
					<td>
						<a href="{{ route('admin.groups.edit', ['id' => $group->id]) }}">
							{{ $group->name }}
						</a>
					</td>
					<td class="text-right">
						<a href="{{ route('admin.groups.members', ['group' => $group->id]) }}">
							{{ $group->members_count }}
						</a>
					</td>
					<td class="text-center">
						@if ($group->datetimecreated->timestamp > $now->timestamp)
							{{ $group->datetimecreated->diffForHumans() }}
						@else
							{{ $group->datetimecreated->format('M d, g:ia') }}
						@endif
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</div>
