@extends('layouts.master')

@section('title'){{ $page->title }}@stop

@if ($page->metadesc)
	@push('meta')
		<meta name="description" content="{{ $page->metadesc }}" />
@endpush
@endif
@if ($page->metakey)
	@push('meta')
		<meta name="keywords" content="{{ $page->metakey }}" />
@endpush
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
		<meta name="{{ $k }}" content="{{ $v }}" />
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
	@if ($page->params->get('show_title', 1))
		<h2>{{ $page->title }}</h2>
		{!! $page->event->afterDisplayTitle; !!}
	@endif

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
	</article>

<?php /*
	@if (auth()->user() && (auth()->user()->can('create pages') || auth()->user()->can('edit pages') || auth()->user()->can('edit.state pages') || auth()->user()->can('delete pages')))
		<!-- <div class="edit-controls">
			@if (auth()->user()->can('create pages'))
				<a href="{{ route('site.pages.create', ['parent_id' => $page->id]) }}" class="edit" title="{{ trans('Create page') }}"><i class="fa fa-plus"></i></a>
			@endif
			@if (auth()->user()->can('edit pages'))
				<a href="{{ route('page', ['uri' => $page->path, 'edit' => 1]) }}" class="edit" data-id="{{ $page->id }}" title="{{ trans('Edit page') }}"><i class="fa fa-pencil"></i></a>
			@endif
			@if (auth()->user()->can('delete pages'))
				<a href="{{ route('page', ['uri' => $page->path, 'delete' => 1]) }}" class="delete" data-id="{{ $page->id }}" data-confirm="{{ trans('pages::pages.confirm delete') }}" title="{{ trans('Delete page') }}"><i class="fa fa-trash"></i></a>
			@endif
			@if (auth()->user()->can('edit.state pages'))
				@if ($page->state)
					<a href="{{ route('page', ['uri' => $page->path, 'state' => 'unpublish']) }}" data-id="{{ $page->id }}" title="{{ trans('Unpublish page') }}"><i class="fa fa-check-circle"></i></a>
				@else
					<a href="{{ route('page', ['uri' => $page->path, 'state' => 'publish']) }}" data-id="{{ $page->id }}" title="{{ trans('Publish page') }}"><i class="fa fa-minus-circle"></i></a>
				@endif
			@endif
		</div> -->

		@if (auth()->user()->can('edit pages') || auth()->user()->can('edit.state pages'))
		<div class="hide" id="article-form{{ $page->id }}">
			<form action="{{ route('site.pages.store', ['uri' => $page->path]) }}" method="post" name="pageform" id="pageform">
				@if (auth()->user()->can('edit pages'))
					<fieldset>
						<legend>{{ trans('pages::pages.page details') }}</legend>

						@if ($page->alias != 'home')
							<div class="form-group">
								<label for="field-parent_id">{{ trans('pages::pages.parent') }}: <span class="required">{{ trans('global.required') }}</span></label>
								<select name="fields[parent_id]" id="field-parent_id" class="form-control">
									<option value="1" data-path="">{{ trans('pages::pages.home') }}</option>
									@foreach ($parents as $p)
										<?php $selected = ($p->id == $page->parent_id ? ' selected="selected"' : ''); ?>
										<option value="{{ $page->id }}"<?php echo $selected; ?> data-path="/{{ $page->path }}"><?php echo str_repeat('|&mdash; ', $page->level) . e($p->title); ?></option>
									@endforeach
								</select>
							</div>
						@else
							<input type="hidden" name="fields[parent_id]" value="{{ $page->parent_id }}" />
						@endif

						<div class="form-group">
							<label for="field-title">{{ trans('pages::pages.title') }}: <span class="required">{{ trans('global.required') }}</span></label>
							<input type="text" name="fields[title]" id="field-title" class="form-control required" maxlength="250" value="{{ $page->title }}" />
						</div>

						<div class="form-group" data-hint="{{ trans('pages::pages.path hint') }}">
							<label for="field-alias">{{ trans('pages::pages.path') }}:</label>
							<div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend">
									<div class="input-group-text">{{ url('/') }}<span id="parent-path">{{ ($page->parent && trim($page->parent->path, '/') ? '/' . $page->parent->path : '') }}</span>/</div>
								</div>
								<input type="text" name="fields[alias]" id="field-alias" class="form-control" maxlength="250"<?php if ($page->alias == 'home'): ?> disabled="disabled"<?php endif; ?> value="{{ $page->alias }}" />
							</div>
							<span class="form-text hint">{{ trans('pages::pages.path hint') }}</span>
						</div>

						<div class="form-group">
							<label for="field-content">{{ trans('pages::pages.content') }}: <span class="required">{{ trans('global.required') }}</span></label>
							<!-- <textarea name="fields[content]" id="field-content" class="form-control" rows="35" cols="40">{{ $page->content }}</textarea> -->
							{!! editor('fields[content]', $page->getOriginal('content'), ['rows' => 35, 'class' => 'required']) !!}
						</div>
					</fieldset>
				@endif

				@if (auth()->user()->can('edit.state pages'))
					<fieldset>
						<legend>{{ trans('global.publishing') }}</legend>

						<div class="form-group">
							<label for="field-access">{{ trans('pages::pages.access') }}:</label>
							<select class="form-control" name="fields[access]" id="field-access"<?php if ($page->isRoot()) { echo ' readonly="readonly" disabled="disabled"'; } ?>>
								<?php foreach (App\Halcyon\Access\Viewlevel::all() as $access): ?>
									<option value="<?php echo $access->id; ?>"<?php if ($page->access == $access->id) { echo ' selected="selected"'; } ?>><?php echo e($access->title); ?></option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="form-group">
							<label for="field-state">{{ trans('pages::pages.state') }}:</label><br />
							<select class="form-control" name="fields[state]" id="field-state"<?php if ($page->isRoot()) { echo ' readonly="readonly" disabled="disabled"'; } ?>>
								<option value="0"<?php if ($page->state == 0) { echo ' selected="selected"'; } ?>>{{ trans('global.unpublished') }}</option>
								<option value="1"<?php if ($page->state == 1) { echo ' selected="selected"'; } ?>>{{ trans('global.published') }}</option>
							</select>
						</div>

						<div class="form-group">
							<label for="field-publish_up">{{ trans('pages::pages.publish up') }}:</label><br />
							{!! Html::input('calendar', 'fields[publish_up]', Carbon\Carbon::parse($page->publish_up ? $page->publish_up : $page->created)) !!}
						</div>

						<div class="form-group">
							<label for="field-publish_down">{{ trans('pages::pages.publish down') }}:</label><br />
							<span class="input-group input-datetime">
								<input type="text" name="fields[publish_down]" id="field-publish_down" class="form-control datetime" value="<?php echo ($page->publish_down ? e(Carbon\Carbon::parse($page->publish_down)->toDateTimeString()) : ''); ?>" placeholder="<?php echo ($page->publish_down ? '' : trans('global.never')); ?>" />
								<span class="input-group-append"><span class="input-group-text icon-calendar"></span></span>
							</span>
						</div>
					</fieldset>
				@endif

				@csrf

				<p class="submit">
					<input class="btn btn-success" type="submit" value="{{ trans('global.save') }}" />
				</p>

				<div class="activity-processor">
					<div class="spinner"><div></div></div>
					<div class="msg"></div>
				</div><!-- / .activity-processor -->
			</form>
		</div>
		<script>
			jQuery(document).ready(function($){
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

						$('#article-form' + id).toggleClass('hide');
						$('#article-content' + id).toggleClass('hide');
					});

					frm
						.on('submit', function(e) {
							e.preventDefault();

							$.ajax($(this).attr('action').nohtml(), {
								data: $(this).serializeArray(),
								files: $(":file", this),
								iframe: true,
								processData: false,
								dataType: 'json',
								success: function(response, status) {
									if (typeof response === "string" ) {
										//data = JSON.parse(response.responseText);
										var data = JSON.parse(response);
									} else {
										var data = response;
									}
								}
							});
						});
			});
		</script>
		@endif
	@endif
*/ ?>
@stop
