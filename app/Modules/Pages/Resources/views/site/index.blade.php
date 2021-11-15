@extends('layouts.master')

@section('title'){{ $page->title }}@stop

@if ($page->metadesc || $page->metakey)
@section('meta')
	@if ($page->metadesc)
		<meta name="description" content="{{ $page->metadesc }}" />
	@endif
	@if ($page->metakey)
		<meta name="keywords" content="{{ $page->metakey }}" />
	@endif
@stop
@endif

@if ($page->metadata)
	@foreach ($page->metadata->all() as $k => $v)
		@if ($v)
			@if ($v == '__comment__')
				@push('meta')
		{!! $k !!}
@endpush
			@else
				@push('meta')
		{!! $v !!}
@endpush
			@endif
		@endif
	@endforeach
@endif

@if (count($page->styles))
	@foreach ($page->styles as $v)
		@push('styles')
			<link rel="stylesheet" type="text/css" href="{{ substr($v, 0, 1) == '/' ? $v : asset($v) }}" />
		@endpush
	@endforeach
@endif

@if (count($page->scripts))
	@foreach ($page->scripts as $v)
		@push('scripts')
			<script src="{{ substr($v, 0, 1) == '/' ? $v : asset($v) }}"></script>
		@endpush
	@endforeach
@endif

@section('content')
	<article id="article-content{{ $page->id }}">


		@if (auth()->user() && (auth()->user()->can('create pages') || auth()->user()->can('edit pages') || auth()->user()->can('edit.state pages') || auth()->user()->can('delete pages')))
			<div class="edit-controls float-right">
				<div class="dropdown btn-group">
					<button class="btn ropdown-toggle" type="button" id="optionsbutton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<span class="fa fa-ellipsis-v" aria-hidden="true"></span><span class="sr-only"> {{ trans('pages::pages.options') }}</span>
					</button>
					<div class="dropdown-menu dropdown-menu-right" aria-labelledby="optionsbutton">
						@if (auth()->user()->can('edit pages'))
							<a href="#page-form{{ $page->id }}" data-id="{{ $page->id }}" class="edit dropdown-item tip" title="{{ trans('global.button.edit') }}">
								<span class="fa fa-pencil" aria-hidden="true"></span> {{ trans('global.button.edit') }}
							</a>
						@endif
						@if (auth()->user()->can('delete pages'))
							<a href="{{ route('site.pages.delete', ['id' => $page->id]) }}" data-id="{{ $page->id }}" class="delete dropdown-item tip" data-confirm="{{ trans('global.confirm delete') }}" data-api="{{ route('api.pages.delete', ['id' => $page->id]) }}" title="{{ trans('global.button.delete') }}">
								<span class="fa fa-trash" aria-hidden="true"></span> {{ trans('global.button.delete') }}
							</a>
						@endif
					</div>
				</div>
			</div>
		@endif
		@if ($page->params->get('show_title', 1))
			<h2>{{ $page->title }}</h2>
			{!! $page->event->afterDisplayTitle; !!}
		@endif
		<div class="article-wrap" id="page-content{{ $page->id }}">
			<?php
			$useDefList = ($page->params->get('show_author') || $page->params->get('show_create_date') || $page->params->get('show_modify_date') || $page->params->get('show_hits'));

			if ($useDefList) : ?>
				<dl class="article-info">
					<dt class="article-info-term">{{ trans('pages::pages.article info') }}</dt>
				<?php if ($page->params->get('show_create_date')) : ?>
					<dd class="create">
						{{ trans('pages::pages.created on', ['date' => $page->created_at->toDateTimeString()]) }}
					</dd>
				<?php endif; ?>
				<?php if ($page->params->get('show_modify_date')) : ?>
					<dd class="updated">
						{{ trans('pages::pages.last updated', ['date' => $page->updated_at->toDateTimeString()]) }}
					</dd>
				<?php endif; ?>
				<?php if ($page->params->get('show_author') && $page->creator->id) : ?>
					<dd class="createdby">
						<?php echo trans('pages::pages.article author', ['author' => $page->creator->name]); ?>
					</dd>
				<?php endif; ?>
				<?php if ($page->params->get('show_hits')): ?>
					<dd class="hits">
						<?php echo trans('pages::pages.article hits', ['hits' => $page->hits]); ?>
					</dd>
				<?php endif; ?>
				</dl>
			<?php endif; ?>

			{!! $page->body !!}
		</div>
	</article>

	@if (auth()->user() && (auth()->user()->can('create pages') || auth()->user()->can('edit pages') || auth()->user()->can('edit.state pages') || auth()->user()->can('delete pages')))
		@if (auth()->user()->can('edit pages') || auth()->user()->can('edit.state pages'))
		<div class="d-none" id="article-form{{ $page->id }}">
			<form action="{{ route('site.pages.store', ['uri' => $page->path]) }}" data-api="{{ route('api.pages.update', ['id' => $page->id]) }}" method="post" name="pageform" id="pageform" class="editform">
				@if (auth()->user()->can('edit pages'))
					<fieldset>
						<legend>{{ trans('global.details') }}</legend>

						@if ($page->alias != 'home')
							<div class="form-group">
								<label for="field-parent_id">{{ trans('pages::pages.parent') }}: <span class="required" data-tip="{{ trans('global.required') }}">*</span></label>
								<select name="parent_id" id="field-parent_id" class="form-control">
									<option value="1" data-path="">{{ trans('pages::pages.home') }}</option>
									@foreach ($parents as $p)
										<?php $selected = ($p->id == $page->parent_id ? ' selected="selected"' : ''); ?>
										<option value="{{ $p->id }}"<?php echo $selected; ?> data-path="/{{ $p->path }}"><?php echo str_repeat('|&mdash; ', $p->level) . e($p->title); ?></option>
									@endforeach
								</select>
							</div>
						@else
							<input type="hidden" name="parent_id" value="{{ $page->parent_id }}" />
						@endif

						<div class="form-group">
							<label for="field-title">{{ trans('pages::pages.title') }}: <span class="required" data-tip="{{ trans('global.required') }}">*</label>
							<input type="text" name="title" id="field-title" class="form-control required" maxlength="250" value="{{ $page->title }}" />
						</div>

						<div class="form-group" data-hint="{{ trans('pages::pages.path hint') }}">
							<label for="field-alias">{{ trans('pages::pages.path') }}:</label>
							<div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend">
									<div class="input-group-text">{{ url('/') }}<span id="parent-path">{{ ($page->parent && trim($page->parent->path, '/') ? '/' . $page->parent->path : '') }}</span>/</div>
								</div>
								<input type="text" name="alias" id="field-alias" aria-describedby="field-alias-hint" class="form-control{{ $errors->has('fields.alias') ? ' is-invalid' : '' }}" maxlength="250"<?php if ($page->alias == 'home'): ?> disabled="disabled"<?php endif; ?> value="{{ $page->alias }}" />
							</div>
							<span class="form-text text-muted">{{ trans('pages::pages.path hint') }}</span>
						</div>

						<div class="form-group">
							<label for="field-content">{{ trans('pages::pages.content') }}: <span class="required" data-tip="{{ trans('global.required') }}">*</span></label>
							<!-- <textarea name="fields[content]" id="field-content" class="form-control" rows="35" cols="40">{{ $page->content }}</textarea> -->
							{!! editor('content', $page->getOriginal('content'), ['rows' => 35, 'class' => 'required']) !!}
						</div>
					</fieldset>
				@endif

				@if (auth()->user()->can('edit.state pages'))
					<fieldset>
						<legend>{{ trans('global.publishing') }}</legend>

						<div class="form-group">
							<label for="field-access">{{ trans('pages::pages.access') }}:</label>
							<select class="form-control" name="access" id="field-access"<?php if ($page->isRoot()) { echo ' readonly="readonly" disabled="disabled"'; } ?>>
								<?php foreach (App\Halcyon\Access\Viewlevel::all() as $access): ?>
									<option value="<?php echo $access->id; ?>"<?php if ($page->access == $access->id) { echo ' selected="selected"'; } ?>><?php echo e($access->title); ?></option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="form-group">
							<label for="field-state">{{ trans('pages::pages.state') }}:</label><br />
							<select class="form-control" name="state" id="field-state"<?php if ($page->isRoot()) { echo ' readonly="readonly" disabled="disabled"'; } ?>>
								<option value="0"<?php if ($page->state == 0) { echo ' selected="selected"'; } ?>>{{ trans('global.unpublished') }}</option>
								<option value="1"<?php if ($page->state == 1) { echo ' selected="selected"'; } ?>>{{ trans('global.published') }}</option>
							</select>
						</div>

						<div class="form-group">
							<label for="field-publish_up">{{ trans('pages::pages.publish up') }}:</label><br />
							<input type="text" name="publish_up" id="field-publish_up" class="form-control datetime" value="<?php echo e(Carbon\Carbon::parse($page->publish_up ? $page->publish_up : $page->created)->toDateTimeString()); ?>" />
						</div>

						<div class="form-group">
							<label for="field-publish_down">{{ trans('pages::pages.publish down') }}:</label><br />
							<input type="text" name="publish_down" id="field-publish_down" class="form-control datetime" value="<?php echo ($page->publish_down ? e(Carbon\Carbon::parse($page->publish_down)->toDateTimeString()) : ''); ?>" placeholder="<?php echo ($page->publish_down ? '' : trans('global.never')); ?>" />
						</div>
					</fieldset>
				@endif

				<input type="hidden" name="id" value="{{ $page->id }}" />
				@csrf

				<p class="text-center">
					<button class="btn btn-success" id="save-page" type="submit">
						{{ trans('global.save') }}
						<span class="spinner-border spinner-border-sm" role="status"><span class="sr-only">{{ trans('global.saving') }}</span></span>
					</button>
					<a href="{{ route('page', ['uri' => $page->path]) }}" data-id="{{ $page->id }}" class="cancel btn btn-link">{{ trans('global.button.cancel') }}</a>
				</p>
			</form>
		</div>
		<script>
			jQuery(document).ready(function($){
				$('[maxlength]').on('keyup', function () {
					var chars = $(this).val().length,
						max = parseInt($(this).data('max-length')),
						ctr = $(this).parent().find('.char-count');

					if (chars) {
						ctr.removeClass('hide');
					} else {
						ctr.addClass('hide');
					}
					ctr.text(max - chars);

					if (chars >= max) {
						var trimmed = $(this).val().substring(0, max);
						$(this).val(trimmed);
					}
				});

				var alias = $('#field-alias');
				if (alias.length) {
					$('#field-title').on('keyup', function () {
						var val = $(this).val();

						val = val.toLowerCase()
							.replace(/\s+/g, '_')
							.replace(/[^a-z0-9\-_]+/g, '');

						alias.val(val);
					});
				}

				$('#field-parent_id')
					.on('change', function () {
						$('#parent-path').html($(this).children("option:selected").data('path'));
					});

				$('#content')
					// Add confirm dialog to delete links
					.on('click', 'a.delete', function (e) {
						var res = confirm($(this).attr('data-confirm'));
						if (!res) {
							e.preventDefault();
						}
						return res;
					})
					.on('click', 'a.edit,a.cancel', function(e){
						e.preventDefault();

						var id = $(this).attr('data-id');

						$('#article-form' + id).toggleClass('d-none');
						$('#article-content' + id).toggleClass('d-none');
					});

				$('#pageform').on('submit', function (e) {
					e.preventDefault();

					var frm = $(this),
						invalid = false;

					var elms = frm.find('input[required]');
					elms.each(function (i, el) {
						if (!el.value || !el.validity.valid) {
							el.classList.add('is-invalid');
							invalid = true;
						} else {
							el.classList.remove('is-invalid');
						}
					});
					elms = frm.find('select[required]');
					elms.each(function (i, el) {
						if (!el.value || el.value <= 0) {
							el.classList.add('is-invalid');
							invalid = true;
						} else {
							el.classList.remove('is-invalid');
						}
					});
					elms = frm.find('textarea[required]');
					elms.each(function (i, el) {
						if (!el.value || !el.validity.valid) {
							el.classList.add('is-invalid');
							invalid = true;
						} else {
							el.classList.remove('is-invalid');
						}
					});

					if (invalid) {
						return false;
					}

					var btn = $('#save-page');
					btn.addClass('processing');

					var post = {},
						k,
						fields = frm.serializeArray();
					for (var i = 0; i < fields.length; i++) {
						if (fields[i].name.substring(0, 6) == 'params') {
							if (typeof (post['params']) === 'undefined') {
								post['params'] = {};
							}
							k = fields[i].name.substring(7);

							post['params'][k.substring(0, k.length - 1)] = fields[i].value;
						} else {
							post[fields[i].name] = fields[i].value;
						}
					}

					$.ajax({
						url: frm.data('api'),
						type: (post['id'] ? 'put' : 'post'),
						data: post,
						dataType: 'json',
						async: false,
						success: function (response) {
							if (response.url) {
								window.location.href = response.url;
							} else {
								window.location.reload();
							}
						},
						error: function (xhr) {
							btn.removeClass('processing');
							frm.prepend('<div class="alert alert-danger">' + xhr.responseJSON.message + '</div>');
						}
					});
				});
			});
		</script>
		@endif
	@endif
@stop
