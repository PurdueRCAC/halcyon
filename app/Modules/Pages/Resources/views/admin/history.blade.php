@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ Module::asset('core:vendor/chartjs/Chart.css') . '?v=' . filemtime(public_path() . '/modules/core/vendor/chartjs/Chart.css') }}" />
@endpush

@push('scripts')
<script src="{{ Module::asset('core:vendor/chartjs/Chart.min.js') . '?v=' . filemtime(public_path() . '/modules/core/vendor/chartjs/Chart.min.js') }}"></script>
<script src="{{ Module::asset('pages:js/pages.js') . '?v=' . filemtime(public_path() . '/modules/pages/js/pages.js') }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('pages::pages.module name'),
		route('admin.pages.index')
	)
	->append(
		trans('pages::pages.history') . ' #' . $row->id
	);
@endphp

@section('toolbar')
	{!! Toolbar::link('back', trans('pages::pages.back'), route('admin.pages.index'), false) !!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('pages::pages.module name') }}: {{ trans('pages::pages.history') . ' #' . $row->id }}
@stop

@section('content')
<form action="{{ route('admin.pages.index') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<div class="card mb-4">
		<table class="table table-hover adminlist">
			<caption>{{ $row->title }}</caption>
			<thead>
				<tr>
					<th scope="col" class="priority-3">
						{!! trans('pages::pages.actor') !!}
					</th>
					<th scope="col">
						{!! trans('pages::pages.action') !!}
					</th>
					<th scope="col" class="priority-2">
						{!! trans('pages::pages.fields') !!}
					</th>
					<th scope="col" class="priority-3">
						{!! trans('pages::pages.datetime') !!}
					</th>
				</tr>
			</thead>
			<tbody>
		@if (count($history))
			@foreach ($history as $i => $action)
				<?php
				$actor = trans('global.unknown');

				if ($action->user):
					$actor = $action->user->name . ' (' . $action->user->username . ')';
				endif;

				$created = $action->created_at ? $action->created_at : trans('global.unknown');

				if (is_object($action->new)):
					$f = get_object_vars($action->new);
				elseif (is_array($action->new)):
					$f = $action->new;
				endif;

				$fields = array_keys($f);

				foreach ($fields as $i => $k):
					if (in_array($k, ['created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at'])):
						unset($fields[$i]);
					endif;
				endforeach;

				$badge = 'info';
				if ($action->action == 'created'):
					$badge = 'success';
				endif;
				if ($action->action == 'deleted'):
					$badge = 'danger';
				endif;

				$old = Carbon\Carbon::now()->subDays(2);
				?>
				<tr>
					<td class="priority-5">
						{{ $actor }}
					</td>
					<td class="priority-2">
						<span class="badge badge-{{ $badge }} entry-action">{{ $action->action }}</span>
					</td>
					<td class="priority-3">
						@if ($action->action == 'updated')
							<span class="entry-diff"><code><?php echo implode('</code>, <code>', $fields); ?></code></span>
						@endif
					</td>
					<td class="priority-4">
						<time datetime="{{ $action->created_at->toDateTimeLocalString() }}">
							@if ($action->created_at < $old)
								{{ $action->created_at->format('d M Y') }}
							@else
								{{ $action->created_at->diffForHumans() }}
							@endif
						</time>
					</td>
				</tr>
			@endforeach
		@else
				<tr>
					<td class="priority-5">
						{{ $row->creator ? $row->creator->name : trans('global.unknown') }}
					</td>
					<td class="priority-2">
						<span class="badge badge-success entry-action">{{ trans('pages::pages.created') }}</span>
					</td>
					<td class="priority-3">
					</td>
					<td class="priority-4">
						<time datetime="{{ $row->created_at->toDateTimeLocalString() }}">
							@if ($row->created_at < Carbon\Carbon::now()->subDays(2))
								{{ $row->created_at->format('d M Y') }}
							@else
								{{ $row->created_at->diffForHumans() }}
							@endif
						</time>
					</td>
				</tr>
		@endif
			</tbody>
		</table>
	</div>

	@csrf
</form>

@stop