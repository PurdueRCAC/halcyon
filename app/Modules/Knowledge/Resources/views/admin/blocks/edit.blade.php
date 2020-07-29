@extends('layouts.master')

@push('scripts')
<script src="{{ asset('js/validate.js?v=' . filemtime(public_path() . '/js/validate.js')) }}"></script>
<script src="{{ asset('modules/knowledge/js/admin.js?v=' . filemtime(public_path() . '/modules/knowledge/js/admin.js')) }}"></script>
@endpush

@section('toolbar')
	@if (auth()->user()->can('edit knowledge'))
		{!! Toolbar::save(route('admin.knowledge.comments.store', ['report' => $report->id])) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.knowledge.comments.cancel', ['report' => $report->id]));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('knowledge.name') !!} Comment: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.knowledge.comments.store', ['report' => $report->id]) }}" method="post" name="adminForm" id="item-form" class="editform form-validate" data-invalid-msg="{{ trans('global.validation failed') }}">

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
					<label for="field-comment">{{ trans('knowledge::knowledge.comment') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<textarea name="fields[comment]" id="field-comment" class="form-control" rows="20" cols="40">{{ $row->comment }}</textarea>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-5">
			<table class="meta">
				<caption>Metadata</caption>
				<tbody>
					<tr>
						<th scope="row">{{ trans('knowledge::knowledge.contactreport id') }}:</th>
						<td>
							{{ $row->contactreportid }}
							<input type="hidden" name="fields[contactreportid]" id="field-contactreportid" value="{{ $row->contactreportid }}" />
						</td>
					</tr>
					<?php if ($row->id): ?>
						<tr>
							<th scope="row">{{ trans('knowledge::knowledge.id') }}:</th>
							<td>
								{{ $row->id }}
								<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />
							</td>
						</tr>
						<tr>
							<th scope="row">{{ trans('knowledge::knowledge.created') }}:</th>
							<td>
								<?php if ($row->getOriginal('datetimecreated') && $row->getOriginal('datetimecreated') != '0000-00-00 00:00:00'): ?>
									{{ $row->datetimecreated }}
								<?php else: ?>
									{{ trans('global.unknown') }}
								<?php endif; ?>
							</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>

			<?php if ($row->id): ?>
				<div class="data-wrap">
					<h4><?php echo trans('knowledge::knowledge.history'); ?></h4>
					<ul class="entry-log">
						<?php
						$prev = 0;
						foreach ($row->history()->orderBy('id', 'desc')->get() as $history):
							$actor = trans('global.unknown');

							if ($history->user):
								$actor = e($history->user->name);
							endif;

							$created = $history->created_at && $history->created_at != '0000-00-00 00:00:00'
								? $history->created_at
								: trans('global.unknown');
							?>
							<li>
								<span class="entry-log-data">{{ trans('knowledge::knowledge.history edited', ['user' => $actor, 'timestamp' => $created]) }}</span>
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