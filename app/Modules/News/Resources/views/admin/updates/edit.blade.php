@extends('layouts.master')

@push('scripts')
<script src="{{ asset('modules/core/js/validate.js?v=' . filemtime(public_path() . '/modules/core/js/validate.js')) }}"></script>
<script src="{{ asset('modules/news/js/admin.js?v=' . filemtime(public_path() . '/modules/news/js/admin.js')) }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('news::news.module name'),
		route('admin.news.index')
	)
	->append(
		'Article # ' . $article->id,
		route('admin.news.index')
	)
	->append(
		trans('news::news.updates'),
		route('admin.news.updates', ['article' => $article->id])
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit knowledge'))
		{!! Toolbar::save(route('admin.news.updates.store', ['article' => $article->id])) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.news.updates.cancel', ['article' => $article->id]));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('news.name') !!} Update: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.news.updates.store', ['article' => $article->id]) }}" method="post" name="adminForm" id="item-form" class="editform form-validate" data-invalid-msg="{{ trans('global.validation failed') }}">

	@if ($errors->any())
		<div class="alert alert-danger">
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	<input type="hidden" name="article" value="{{ $article->id }}" />
	<input type="hidden" name="fields[newsid]" value="{{ $article->id }}" />

	<div class="row">
		<div class="col col-md-7">
			<fieldset class="adminform">
				<legend><span>{{ trans('global.details') }}</span></legend>

				<div class="form-group">
					<label for="field-body">{{ trans('news::news.body') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<textarea name="fields[body]" id="field-body" class="form-control" rows="20" cols="40">{{ $row->body }}</textarea>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-5">
			<!-- <table class="meta">
				<caption>Metadata</caption>
				<tbody>
					<tr>
						<th scope="row">{{ trans('news::news.news id') }}:</th>
						<td>
							{{ $row->newsid }}
							<input type="hidden" name="fields[newsid]" id="field-newsid" value="{{ $row->newsid }}" />
						</td>
					</tr>
					<?php if ($row->id): ?>
						<tr>
							<th scope="row">{{ trans('news::news.id') }}:</th>
							<td>
								{{ $row->id }}
								<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />
							</td>
						</tr>
						<tr>
							<th scope="row">{{ trans('news::news.created') }}:</th>
							<td>
								<?php if ($row->getOriginal('datetimecreated') && $row->getOriginal('datetimecreated') != '0000-00-00 00:00:00'): ?>
									{{ $row->datetimecreated }}
								<?php else: ?>
									{{ trans('global.unknown') }}
								<?php endif; ?>
							</td>
						</tr>
					<?php endif; ?>
					<?php if ($row->getOriginal('datetimeedited') && $row->getOriginal('datetimeedited') != '0000-00-00 00:00:00'): ?>
						<tr>
							<th scope="row">{{ trans('news::news.modified') }}:</th>
							<td>
								{{ $row->datetimeedited }}
							</td>
						</tr>
					<?php endif; ?>
					<?php if ($row->getOriginal('datetimeremoved') && $row->getOriginal('datetimeremoved') != '0000-00-00 00:00:00'): ?>
						<tr>
							<th scope="row">{{ trans('news::news.removed') }}:</th>
							<td>
								{{ $row->datetimeremoved }}
							</td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table> -->

			<fieldset class="adminform">
				<legend><span>{{ trans('global.publishing') }}</span></legend>

				<div class="input-wrap form-group">
					<label for="field-published">{{ trans('pages::pages.state') }}:</label>
					<select name="published" id="field-published" class="form-control">
						<option value="published"<?php if (!$row->datetimeremoved || $row->datetimeremoved == '0000-00-00 00:00:00' || $row->datetimeremoved == '-0001-11-30 00:00:00') { echo ' selected="selected"'; } ?>>{{ trans('global.published') }}</option>
						<option value="trashed"<?php if ($row->datetimeremoved && $row->datetimeremoved != '0000-00-00 00:00:00' && $row->datetimeremoved != '-0001-11-30 00:00:00') { echo ' selected="selected"'; } ?>>{{ trans('global.trashed') }}</option>
					</select>
				</div>
			</fieldset>

			<?php if ($row->id): ?>
				<div class="data-wrap">
					<h4><?php echo trans('pages::pages.history'); ?></h4>
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
								<span class="entry-log-data">{{ trans('news::news.history edited', ['user' => $actor, 'timestamp' => $created]) }}</span>
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