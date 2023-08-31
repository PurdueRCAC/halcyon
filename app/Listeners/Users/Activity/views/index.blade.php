
<div class="contentInner">
	<h2>{{ trans('history::history.activity') }}</h2>

	<form action="{{ route('site.users.account.section', ['section' => 'activity']) }}" method="get" name="activity">

		<div class="row">
			<div class="col filter-search col-md-6">
				<label class="sr-only" for="filter_action">{{ trans('history::history.transport') }}</label>
				<select name="action" id="filter_action" class="form-control filter filter-submit">
					<option value=""<?php if ($filters['action'] == ''): echo ' selected="selected"'; endif;?>>{{ trans('history::history.all actions') }}</option>
					<option value="emailed"<?php if ($filters['action'] == 'emailed'): echo ' selected="selected"'; endif;?>>Emailed</option>
					<option value="created"<?php if ($filters['action'] == 'created'): echo ' selected="selected"'; endif;?>>Created/Added</option>
					<option value="updated"<?php if ($filters['action'] == 'updated'): echo ' selected="selected"'; endif;?>>Updated</option>
					<option value="deleted"<?php if ($filters['action'] == 'deleted'): echo ' selected="selected"'; endif;?>>Deleted/Removed</option>
				</select>
			</div>
			<div class="col filter-search col-md-4">
				<label class="sr-only" for="filter_status">{{ trans('history::history.status') }}</label>
				<select name="status" id="filter_status" class="form-control filter filter-submit">
					<option value=""<?php if ($filters['status'] == ''): echo ' selected="selected"'; endif;?>>{{ trans('history::history.all status') }}</option>
					<option value="success"<?php if ($filters['status'] == 'success'): echo ' selected="selected"'; endif;?>>{{ trans('listener.users.activity::activity.success') }}</option>
					<option value="error"<?php if ($filters['status'] == 'error'): echo ' selected="selected"'; endif;?>>{{ trans('listener.users.activity::activity.error') }}</option>
				</select>
			</div>
			<div class="col filter-search col-md-2 text-right">
				<button class="btn btn-secondary" type="submit">{{ trans('listener.users.activity::activity.filter') }}</button>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

	@if (count($history) > 0)
		<table class="table table-hover adminlist">
			<caption class="sr-only">{{ trans('history::history.activity') }}</caption>
			<thead>
				<tr>
					<th scope="col" class="text-center">
						{!! Html::grid('sort', trans('history::history.status'), 'status', $filters['order_dir'], $filters['order']) !!}
					</th>
					<th scope="col">
						{!! Html::grid('sort', trans('history::history.timestamp'), 'datetime', $filters['order_dir'], $filters['order']) !!}
					</th>
					<th scope="col">
						{{ trans('listener.users.activity::activity.summary') }}
					</th>
				</tr>
			</thead>
			<tbody>
			@foreach ($history as $i => $row)
				<?php
				$row = $row->process($row);

				$cls = '';
				if ($row->status >= 400):
					$cls = ' class="error-danger text-danger"';
				endif;
				?>
				<tr{!! $cls !!} data-id="{{ $row->id }}">
					<td class="text-center">
						@if ($row->status >= 400)
							<span class="fa fa-exclamation-triangle text-danger" aria-hidden="true"></span>
							<span class="sr-only">{{ trans('listener.users.activity::activity.error') }} - {{ $row->status }}</span>
						@else
							<span class="fa fa-check-circle text-success" aria-hidden="true"></span>
							<span class="sr-only">{{ trans('listener.users.activity::activity.success') }}</span>
						@endif
					</td>
					<td>
						@if ($row->datetime)
							<time datetime="{{ $row->datetime->toDateTimeLocalString() }}">
								@if ($row->datetime->format('Y-m-dTh:i:s') > Carbon\Carbon::now()->toDateTimeString())
									{{ $row->datetime->diffForHumans() }}
								@else
									{{ $row->datetime->format('M j, Y g:ia') }}
								@endif
							</time>
						@else
							<span class="text-muted never">{{ trans('global.unknown') }}</span>
						@endif
					</td>
					<td>
						@if ($row->app == 'email')
							Emailed:
						@endif
						{!! $row->summary ? $row->summary : $row->payload !!}
					</td>
				</tr>
			@endforeach
			</tbody>
		</table>

		{{ $history->render() }}

	@else
		<div class="d-flex justify-content-center">
			<div class="card card-help w-50">
				<div class="card-body">
					<h3 class="card-title mt-0">What is this page?</h3>
					<p class="card-text">Here you can find the activity history for {{ $user->name }}.</p>
				</div>
			</div>
		</div>
	@endif

	</form>
</div>