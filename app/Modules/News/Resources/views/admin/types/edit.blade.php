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
		trans('news::news.types'),
		route('admin.news.types')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit news.types'))
		{!! Toolbar::save(route('admin.news.types.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.news.types.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('news.name') !!}: {{ trans('news::news.types') }}: {{ $row->id ? 'Edit: #' . $row->id : 'Create' }}
@stop

@section('content')
<form action="{{ route('admin.news.types.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate" data-invalid-msg="{{ trans('global.validation failed') }}">

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
					<label for="field-name">{{ trans('news::news.name') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="fields[name]" id="field-name" class="form-control required" size="30" maxlength="250" value="{{ $row->name }}" />
				</div>

				<div class="row">
					<div class="col col-md-6">
						<div class="form-block">
						<div class="form-group form-check">
							<input type="checkbox" name="fields[location]" id="field-location" class="form-check-input" value="1" <?php if ($row->location): ?>checked="checked"<?php endif; ?> />
							<label for="field-location" class="form-check-label">{{ trans('news::news.location') }}</label>
							<span class="form-text">Allow for specifying a location on articles in this category?</span>
						</div>
						</div>
					</div>
					<div class="col col-md-6">
						<div class="form-block">
						<div class="form-group form-check">
							<input type="checkbox" name="fields[future]" id="field-future" class="form-check-input" value="1" <?php if ($row->future): ?>checked="checked"<?php endif; ?> />
							<label for="field-future" class="form-check-label">{{ trans('news::news.future') }}</label>
							<span class="form-text">Display future events in listings?</span>
						</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col col-md-6">
						<div class="form-block">
						<div class="form-group form-check">
							<input type="checkbox" name="fields[ongoing]" id="field-ongoing" class="form-check-input" value="1" <?php if ($row->ongoing): ?>checked="checked"<?php endif; ?> />
							<label for="field-ongoing" class="form-check-label">{{ trans('news::news.ongoing') }}</label>
							<span class="form-text">Allow for specifying a location on articles in this category?</span>
						</div>
						</div>
					</div>
					<div class="col col-md-6">
						<div class="form-block">
						<div class="form-group form-check">
							<input type="checkbox" name="fields[url]" id="field-url" class="form-check-input" value="1" <?php if ($row->url): ?>checked="checked"<?php endif; ?> />
							<label for="field-url" class="form-check-label">{{ trans('news::news.url') }}</label>
							<span class="form-text">Allow for specifying a URL on articles in this category?</span>
						</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col col-md-6">
						<div class="form-block">
						<div class="form-group form-check">
							<input type="checkbox" name="fields[tagresources]" id="field-tagresources" class="form-check-input" value="1" <?php if ($row->tagresources): ?>checked="checked"<?php endif; ?> />
							<label for="field-tagresources" class="form-check-label">{{ trans('news::news.tag resources') }}</label>
							<span class="form-text">Allow for tagging resources on articles in this category?</span>
						</div>
						</div>
					</div>
					<div class="col col-md-6">
						<div class="form-block">
						<div class="form-group form-check">
							<input type="checkbox" name="fields[tagusers]" id="field-tagusers" class="form-check-input" value="1" <?php if ($row->tagusers): ?>checked="checked"<?php endif; ?> />
							<label for="field-tagusers" class="form-check-label">{{ trans('news::news.tag users') }}</label>
							<span class="form-text">Allow for tagging users on articles in this category?</span>
						</div>
						</div>
					</div>
				</div>
			</fieldset>
		</div>
		<div class="col col-md-5">
			@include('history::admin.history')
			@if ($row->id)
				<!-- <div class="card history">
					<h4 class="card-header">{{ trans('history.title') }}</h4>
					<div class="card-body">
					<ul class="timeline">
						<?php
						$history = $row->history()
							->orderBy('created_at', 'desc')
							->get();

						if (count($history)):
							foreach ($history as $action):
								$actor = trans('global.unknown');

								if ($action->user):
									$actor = e($action->user->name);
								endif;

								$created = $action->created_at && $action->created_at != '0000-00-00 00:00:00' ? $action->created_at : trans('global.unknown');

								$fields = array_keys(get_object_vars($action->new));
								foreach ($fields as $i => $k)
								{
									if (in_array($k, ['created_at', 'updated_at', 'deleted_at']))
									{
										unset($fields[$i]);
									}
								}
								?>
								<li>
									<span class="history-date">{{ $created }}</span><br />
									<span class="history-action">{{ trans('history.action by', ['user' => $actor, 'datetime' => $created]) }}</span><br />
									<span class="history-diff">Changed fields: <?php echo implode(', ', $fields); ?></span>
								</li>
								<?php
							endforeach;
						else:
							?>
							<li>
								<span class="history-diff">{{ trans('history.none') }}</span>
							</li>
							<?php
						endif;
						?>
					</ul>
					</div>
				</div> -->
			@endif
		</div>
	</div>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />
	@csrf
</form>
@stop