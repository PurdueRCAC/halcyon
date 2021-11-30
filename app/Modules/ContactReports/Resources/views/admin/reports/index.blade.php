@extends('layouts.master')

@section('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.css?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css?v=' . filemtime(public_path() . '/modules/core/vendor/select2/css/select2.css')) }}" />
@stop

@push('scripts')
<script src="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.js?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
<script src="{{ Module::asset('core:vendor/chartjs/Chart.min.js') . '?v=' . filemtime(public_path() . '/modules/core/vendor/chartjs/Chart.min.js') }}"></script>
<script src="{{ asset('modules/contactreports/js/admin.js?v=' . filemtime(public_path() . '/modules/contactreports/js/admin.js')) }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('contactreports::contactreports.module name'),
		route('admin.contactreports.index')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete contactreports'))
		{!! Toolbar::deleteList('', route('admin.contactreports.delete')) !!}
	@endif

	@if (auth()->user()->can('create contactreports'))
		{!! Toolbar::addNew(route('admin.contactreports.create')) !!}
	@endif

	@if (auth()->user()->can('admin contactreports'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('contactreports')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('contactreports.name') !!}
@stop

@section('panel')
	<h2 class="sr-only">Contact Reports Stats</h2>

	<div class="car mb-3">
		<form action="{{ route('admin.contactreports.index') }}" method="post" name="statsForm">
			<label for="timeframe" class="sr-only">Stats for</label>
			<select name="timeframe" id="timeframe" class="form-control filter-submit">
				<option value="1"<?php if ($filters['timeframe'] == 1) { echo ' selected="slected"'; } ?>>Past month</option>
				<option value="6"<?php if ($filters['timeframe'] == 6) { echo ' selected="slected"'; } ?>>Past 6 months</option>
				<option value="12"<?php if ($filters['timeframe'] == 12) { echo ' selected="slected"'; } ?>>Past year</option>
				<option value="*"<?php if ($filters['timeframe'] == '*') { echo ' selected="slected"'; } ?>>All time</option>
			</select>

			@csrf
		</form>
	</div>

	<div class="card mb-3">
		<div class="card-body">
			<h4 class="mt-0 card-title">By Type</h4>
			<?php
			$stats = array();
			foreach ($types as $type):
				if ($filters['timeframe'] == '*'):
					$val = $type->reports()->count();
				else:
					$val = $type->reports()->where('datetimecreated', '>=', Carbon\Carbon::now()->modify('-' . $filters['timeframe'] . ' months')->toDateTimeString())->count();
				endif;

				if ($val):
					$stats[$type->name] = $val;
				endif;
			endforeach;
			?>
			<div>
				<canvas id="breakdown-types" class="pie-chart" width="275" height="500" data-labels="{{ json_encode(array_keys($stats)) }}" data-values="{{ json_encode(array_values($stats)) }}">
					@foreach ($stats as $name => $val)
						{{ $name }}: $val<br />
					@endforeach
				</canvas>
			</div>
		</div>
	</div>

	<?php
	$r = (new App\Modules\ContactReports\Models\Reportresource)->getTable();
	$c = (new App\Modules\ContactReports\Models\Report)->getTable();

	$resources = App\Modules\ContactReports\Models\Reportresource::query()
		->select($r . '.resourceid', DB::raw('COUNT(*) as total'))
		->join($c, $c . '.id', $r . '.contactreportid')
		->where($c . '.datetimecreated', '>=', Carbon\Carbon::now()->modify('-' . $filters['timeframe'] . ' months')->toDateTimeString())
		->groupBy($r . '.resourceid')
		->orderBy('total', 'desc')
		->limit(5)
		->get();
	?>
	<div class="card mb-3">
		<table class="table">
			<caption>Top Resources</caption>
			<tbody>
				@foreach ($resources as $res)
					<tr>
						<th scope="row"><span class="badge badge-info">{{ $res->resource->name }}</span></th>
						<td class="text-right">{{ $res->total }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
@stop

@section('content')
@component('contactreports::admin.submenu')
	reports
@endcomponent
<form action="{{ route('admin.contactreports.index') }}" data-api="{{ route('api.contactreports.index') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-3">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
			<div class="col col-md-3 text-right">
				<label class="sr-only" for="filter_contactreporttypeid">{{ trans('contactreports::contactreports.type') }}</label>
				<select name="type" id="filter_contactreporttypeid" class="form-control filter filter-submit">
					<option value="*"<?php if ($filters['type'] == '*') { echo ' selected="selected"'; } ?>>{{ trans('contactreports::contactreports.all types') }}</option>
					<option value="0"<?php if (!$filters['type']) { echo ' selected="selected"'; } ?>>{{ trans('global.none') }}</option>
					@foreach ($types as $type)
						<option value="{{ $type->id }}"<?php if ($filters['type'] == $type->id) { echo ' selected="selected"'; } ?>>{{ $type->name }}</option>
					@endforeach
				</select>
			</div>
			<div class="col col-md-3">
				<label class="sr-only" for="filter_start">{{ trans('contactreports::contactreports.start') }}</label>
				<span class="input-group">
					<input type="text" name="start" id="filter_start" class="form-control filter filter-submit date" value="{{ $filters['start'] }}" placeholder="Start date" />
					<span class="input-group-append"><span class="input-group-text"><span class="fa fa-calendar" aria-hidden="true"></span></span>
				</span>
			</div>
			<div class="col col-md-3">
				<label class="sr-only" for="filter_stop">{{ trans('contactreports::contactreports.stop') }}</label>
				<span class="input-group">
					<input type="text" name="stop" id="filter_stop" class="form-control filter filter-submit date" value="{{ $filters['stop'] }}" placeholder="End date" />
					<span class="input-group-append"><span class="input-group-text"><span class="fa fa-calendar" aria-hidden="true"></span></span></span>
				</span>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	<div id="results">
	@if (count($rows))
		@foreach ($rows as $i => $row)
			<div class="card mb-3">
				<div class="card-header">
					<div class="d-flex">
						@if (auth()->user()->can('delete issues'))
							<div>
								{!! Html::grid('id', $i, $row->id) !!}
							</div>
						@endif
						<div class="text-muted ml-4">
							<span class="fa fa-calendar" aria-hidden="true"></span>
							@if ($row->datetimecreated)
								<time datetime="{{ $row->datetimecreated->format('Y-m-dTh:i:s') }}">
									@if ($row->datetimecreated->format('Y-m-dTh:i:s') > Carbon\Carbon::now()->toDateTimeString())
										{{ $row->datetimecreated->diffForHumans() }}
									@else
										{{ $row->datetimecreated->format('M d, Y') }}
									@endif
								</time>
							@else
								<span class="never">{{ trans('global.unknown') }}</span>
							@endif
						</div>
						<div class="text-muted ml-4">
							<?php
							$uids = $row->users->pluck('userid')->toArray();

							$users = array();
							if ($row->userid && !in_array($row->userid, $uids)):
								$users[] = ($row->creator ? '<a href="' . route('admin.users.show', ['id' => $row->userid]) . '"class="badg badg-primary">' . $row->creator->name . '</a>' : $row->userid . ' <span class="unknown">' . trans('global.unknown') . '</span>');
							endif;

							foreach ($row->users as $user):
								$u  = '<a href="' . route('admin.users.show', ['id' => $user->userid]) . '" class="badg badg-primary">';
								$u .= ($user->user ? $user->user->name : $user->userid . ' <span class="unknown">' . trans('global.unknown') . '</span>');
								
								if ($user->notified()):
									$u .= ' <span class="fa fa-envelope" data-tip="Followup email sent on ' . $user->datetimelastnotify->toDateTimeString() . '"><time class="sr-only" datetime="' . $user->datetimelastnotify->toDateTimeString() . '">' . $user->datetimelastnotify->format('Y-m-d') . '</time></span>';
								endif;
								$u .= '</a>';

								$users[] = $u;
							endforeach;
							?>
							@if (count($users))
								<span class="fa fa-user" aria-hidden="true"></span>
								{!! implode(', ', $users) !!}
							@endif
						</div>
						<div class="flex-fill text-right">
							@if (auth()->user()->can('edit contactreports'))
								<a href="{{ route('admin.contactreports.edit', ['id' => $row->id]) }}">
									<span class="fa fa-pencil" aria-hidden="true"></span>
									<span class="sr-only"># {{ $row->id }}</span>
								</a>
							@else
								# {{ $row->id }}
							@endif
						</div>
					</div>
				</div>
				<div class="card-body">
					{!! $row->formattedReport !!}
					<?php $row->hashTags; ?>
					@if (count($row->tags))
						<p>
						@foreach ($row->tags as $tag)
							<a class="tag badge badge-sm badge-secondary" href="{{ route('admin.issues.index', ['tag' => $tag->slug]) }}">{{ $tag->name }}</a>
						@endforeach
						</p>
					@endif
					@if (count($row->resources))
						<?php
						$names = array();

						foreach ($row->resources as $res):
							if ($res->resource):
								$names[] = '<span class="badge badge-info">' . e($res->resource->name) . '</span>';
							else:
								$names[] = $res->resourceid;
							endif;
						endforeach;

						asort($names);

						if (count($names)):
							echo '<p>' . implode(' ', $names) . '</p>';
						endif;
						?>
					@endif
				</div>
				<div class="card-footer">
					<div class="d-flex text-muted">
						<div class="flex-fill">
							<span class="fa fa-folder" aria-hidden="true"></span>
							{{ $row->type ? $row->type->name : trans('global.none') }}
						</div>
						<div class="flex-fill text-right">
							<span class="fa fa-comment" aria-hidden="true"></span>
							@if ($row->comments_count)
								<a href="#comments_{{ $row->id }}" class="comments-show">{{ $row->comments_count }}</a>
							@else
								<span class="none">{{ $row->comments_count }}</span>
							@endif
						</div>
					</div>
				</div>
			</div>
			<?php
			$comments = $row->comments()->orderBy('datetimecreated', 'asc')->get();

			if (count($comments) > 0):
				?>
				<div class="card ml-4 mb-3 hide" id="comments_{{ $row->id }}">
					<ul class="list-group w-100">
						@foreach ($comments as $comment)
							<li id="comment_{{ $comment->id }}" class="list-group-item" data-api="{{ route('api.contactreports.comments.update', ['id' => $comment->id]) }}">
								{!! $comment->formattedComment !!}

								<div class="d-flex text-muted">
									<div class="mr-4">
										<span class="fa fa-calendar" aria-hidden="true"></span>
										<time datetime="{{ $comment->datetimecreated->format('Y-m-dTh:i:s') }}">
											{{ $comment->datetimecreated->format('M d, Y') }} @ {{ $comment->datetimecreated->format('h:i a') }}
										</time>
									</div>
									<div class="mr-4">
										@if ($comment->creator)
											<span class="fa fa-user" aria-hidden="true"></span>
											{{ $comment->creator->name }}
										@endif
									</div>
									<div class="flex-fill text-right">
										<span class="badge badge-success<?php if (!$comment->resolution) { echo ' hide'; } ?>">{{ trans('issues::issues.resolution') }}</span>
									</div>
								</div>
							</li>
						@endforeach
					</ul>
				</div>
				<?php
			endif;
			?>
		@endforeach

		{{ $rows->render() }}
	@else
		<div class="card mb-4">
			<div class="card-body text-muted text-center">{{ trans('global.no results') }}</div>
		</div>
	@endif
	</div>

	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>

@stop