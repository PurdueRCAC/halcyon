<?php
/**
 * View for Activity widget
 */
?>
<div class="card mb-4 widget {{ $widget->module }}">
	<div class="overview-container">
		<table class="table stats-overview">
			<thead>
				<th scope="col">{{ trans('widget.activity::activity.timestamp') }}</th>
				<th scope="col">{{ trans('widget.activity::activity.transportmethod') }}</th>
				<th scope="col">{{ trans('widget.activity::activity.classname') }}</th>
				<th scope="col">{{ trans('widget.activity::activity.classmethod') }}</th>
			</thead>
			<tbody>
				@foreach ($activity as $act)
				<tr>
					<td>
						{{ $act->datetime }}
					</td>
					<td>
						{{ $act->transportmethod }}
					</td>
					<td>
						{{ $act->classname }}
					</td>
					<td>
						{{ $act->classmethod }}
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</div>
