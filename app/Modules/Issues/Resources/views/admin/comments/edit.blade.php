@extends('layouts.master')

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('issues::issues.module name'),
		route('admin.issues.index')
	)
	->append(
		trans('issues::issues.comments'),
		route('admin.issues.comments')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit issues'))
		{!! Toolbar::save(route('admin.issues.comments.store', ['report' => $report->id])) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.issues.comments.cancel', ['report' => $report->id]));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('issues::issues.module name') }} Comment: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@section('content')
<form action="{{ route('admin.issues.comments.store', ['report' => $report->id]) }}" method="post" name="adminForm" id="item-form" class="editform form-validate">

	@if ($errors->any())
		<div class="alert alert-danger">
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	<div class="row">
		<div class="col col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-comment">{{ trans('issues::issues.comment') }} <span class="required">{{ trans('global.required') }}</span></label>
					<textarea name="fields[comment]" id="field-comment" class="form-control" rows="20" cols="40">{{ $row->comment }}</textarea>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-5">
			<table class="meta">
				<caption class="sr-only">{{ trans('global.metadata') }}</caption>
				<tbody>
					<tr>
						<th scope="row">{{ trans('issues::issues.contactreport id') }}</th>
						<td>
							{{ $row->contactreportid }}
							<input type="hidden" name="fields[contactreportid]" id="field-contactreportid" value="{{ $row->contactreportid }}" />
						</td>
					</tr>
					@if ($row->id)
						<tr>
							<th scope="row">{{ trans('issues::issues.id') }}</th>
							<td>
								{{ $row->id }}
								<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />
							</td>
						</tr>
						<tr>
							<th scope="row">{{ trans('issues::issues.created') }}</th>
							<td>
								@if ($row->datetimecreated)
									{{ $row->datetimecreated->format('Y-m-d h:i:s') }}
								@else
									{{ trans('global.unknown') }}
								@endif
							</td>
						</tr>
					@endif
				</tbody>
			</table>

			<?php if ($row->id): ?>
				<div class="data-wrap">
					<h4><?php echo trans('issues::issues.history'); ?></h4>
					<ul class="entry-log">
						<?php
						$prev = 0;
						foreach ($row->history()->orderBy('id', 'desc')->get() as $history):
							$actor = trans('global.unknown');

							if ($history->user):
								$actor = e($history->user->name);
							endif;

							$created = $history->created_at
								? $history->created_at
								: trans('global.unknown');
							?>
							<li>
								<span class="entry-log-data">{{ trans('issues::issues.history edited', ['user' => $actor, 'timestamp' => $created]) }}</span>
								<span class="entry-diff"></span>
							</li>
							<?php
						endforeach;
						?>
					</ul>
				</div>
			<?php endif; ?>
		</div>
	</div>

	@csrf
</form>
@stop
