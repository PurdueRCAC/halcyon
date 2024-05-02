@extends('layouts.master')
@php
$page->gatherMetadata();
@endphp

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
			@php
			if (substr($v, 0, 4) != 'http' && substr($v, 0, 3) != '://'):
				$pth = asset($v);
				if (file_exists(public_path($v))):
					$pth .= '?v=' . filemtime(public_path($v));
				endif;
				$v = $pth;
			endif;
			@endphp
			<link rel="stylesheet" type="text/css" href="{{ $v }}" />
		@endpush
	@endforeach
@endif

@if (count($page->scripts))
	@foreach ($page->scripts as $v)
		@push('scripts')
			@php
			if (substr($v, 0, 4) != 'http' && substr($v, 0, 3) != '://'):
				$pth = asset($v);
				if (file_exists(public_path($v))):
					$pth .= '?v=' . filemtime(public_path($v));
				endif;
				$v = $pth;
			endif;
			@endphp
			<script src="{{ $v }}"></script>
		@endpush
	@endforeach
@endif

@section('class')page-{{ str_replace('/', '-', $page->path) . ($page->params->get('container_class') ? ' ' . $page->params->get('container_class') : '') }}@stop

@section('content')
	<article id="article-content{{ $page->id }}">
		@if (auth()->user() && (auth()->user()->can('create pages') || auth()->user()->can('edit pages') || auth()->user()->can('edit.state pages') || auth()->user()->can('delete pages')))
			<div class="edit-controls float-right float-end">
				<div class="dropdown btn-group">
					<button class="btn ropdown-toggle" type="button" id="optionsbutton" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<span class="fa fa-ellipsis-v" aria-hidden="true"></span><span class="sr-only visually-hidden"> {{ trans('pages::pages.options') }}</span>
					</button>
					<div class="dropdown-menu dropdown-menu-right" aria-labelledby="optionsbutton">
						@if (auth()->user()->can('edit pages'))
							<a href="#article-form{{ $page->id }}" data-toggle="modal" data-bs-toggle="modal"
								data-target="#article-form{{ $page->id }}" data-id="{{ $page->id }}" class="edit dropdown-item tip" title="{{ trans('global.button.edit') }}">
								<span class="fa fa-fw fa-pencil mr-1" aria-hidden="true"></span>{{ trans('global.button.edit') }}
							</a>
						@endif
						@if (auth()->user()->can('create pages'))
							<a href="{{ route('site.pages.create', ['parent_id' => $page->id]) }}" data-id="{{ $page->id }}" class="create dropdown-item tip" title="{{ trans('pages::pages.create sub page') }}">
								<span class="fa fa-fw fa-plus mr-1" aria-hidden="true"></span>{{ trans('pages::pages.create sub page') }}
							</a>
						@endif
						@if (auth()->user()->can('delete pages'))
							<a href="{{ route('site.pages.delete', ['id' => $page->id]) }}" data-id="{{ $page->id }}" class="delete-page dropdown-item tip" data-confirm="{{ trans('global.confirm delete') }}" data-api="{{ route('api.pages.delete', ['id' => $page->id]) }}" title="{{ trans('global.button.delete') }}">
								<span class="fa fa-fw fa-trash mr-1" aria-hidden="true"></span>{{ trans('global.button.delete') }}
							</a>
						@endif
					</div>
				</div>
			</div>
		@endif
		@if ($page->params->get('show_title', 1))
			<h2>{{ $page->title }}</h2>
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

	@if (auth()->user() && (auth()->user()->can('edit pages') || auth()->user()->can('edit.state pages') || auth()->user()->can('delete pages')))
		@if (auth()->user()->can('edit pages') || auth()->user()->can('edit.state pages'))
		<div id="article-form{{ $page->id }}" class="modal fade" tabindex="-1" aria-labelledby="article-form{{ $page->id }}-title" aria-hidden="true">
			<div class="modal-dialog modal-lg modal-dialog-centered">
				<form action="{{ route('site.pages.store', ['uri' => $page->path]) }}" data-api="{{ route('api.pages.update', ['id' => $page->id]) }}" method="post" name="pageform" id="pageform" class="modal-content editform">
					<div class="modal-header">
						<h3 class="modal-title" id="article-form{{ $page->id }}-title">{{ trans('global.edit') }}</h3>
						<button type="button" class="btn-close close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
							<span class="visually-hidden" aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="alert alert-danger d-none"></div>

						@if (auth()->user()->can('edit pages'))
							@if ($page->alias != 'home')
								<div class="form-group">
									<label class="form-label" for="field-parent_id">{{ trans('pages::pages.parent') }}: <span class="required" data-tip="{{ trans('global.required') }}">*</span></label>
									<select name="parent_id" id="field-parent_id" class="form-control">
										<option value="1" data-path="">{{ trans('pages::pages.home') }}</option>
										@foreach ($parents as $p)
											<?php $selected = ($p->id == $page->parent_id ? ' selected="selected"' : ''); ?>
											<option value="{{ $p->id }}"<?php echo $selected; ?> data-path="/{{ $p->path }}"><?php echo str_repeat('|&mdash; ', $p->level) . e($p->title); ?></option>
										@endforeach
									</select>
								</div>
							@else
								<input type="hidden" name="parent_id" id="field-parent_id" value="{{ $page->parent_id }}" />
							@endif

							<div class="form-group">
								<label class="form-label" for="field-title">{{ trans('pages::pages.title') }}: <span class="required" data-tip="{{ trans('global.required') }}">*</span></label>
								<input type="text" name="title" id="field-title" class="form-control required" maxlength="250" value="{{ $page->title }}" />
							</div>

							<div class="form-group">
								<label class="form-label" for="field-alias">{{ trans('pages::pages.path') }}:</label>
								<div class="input-group mb-2 mr-sm-2">
									<div class="input-group-prepend">
										<div class="input-group-text">{{ url('/') }}<span id="parent-path">{{ ($page->parent && trim($page->parent->path, '/') ? '/' . $page->parent->path : '') }}</span>/</div>
									</div>
									<input type="text" name="alias" id="field-alias" aria-describedby="field-alias-hint" class="form-control{{ $errors->has('fields.alias') ? ' is-invalid' : '' }}" maxlength="250"<?php if ($page->alias == 'home'): ?> disabled="disabled"<?php endif; ?> value="{{ $page->alias }}" />
								</div>
								<span class="form-text text-muted">{{ trans('pages::pages.path hint') }}</span>
							</div>

							<div class="form-group">
								<label class="form-label" for="field-content">{{ trans('pages::pages.content') }}: <span class="required" data-tip="{{ trans('global.required') }}">*</span></label>
								{!! editor('content', $page->getOriginal('content'), ['rows' => 35, 'class' => 'required', 'id' => 'field-content']) !!}
							</div>
						@endif

						@if (auth()->user()->can('edit.state pages'))
							<div class="row">
								<div class="form-group col-md-6">
									<label class="form-label" for="field-access">{{ trans('pages::pages.access') }}:</label>
									<select class="form-control" name="access" id="field-access"<?php if ($page->isRoot()) { echo ' readonly="readonly" disabled="disabled"'; } ?>>
										<?php foreach (App\Halcyon\Access\Viewlevel::all() as $access): ?>
											<option value="<?php echo $access->id; ?>"<?php if ($page->access == $access->id) { echo ' selected="selected"'; } ?>><?php echo e($access->title); ?></option>
										<?php endforeach; ?>
									</select>
								</div>

								<div class="form-group col-md-6">
									<label class="form-label" for="field-state">{{ trans('pages::pages.state') }}:</label>
									<select class="form-control" name="state" id="field-state"<?php if ($page->isRoot()) { echo ' readonly="readonly" disabled="disabled"'; } ?>>
										<option value="0"<?php if ($page->state == 0) { echo ' selected="selected"'; } ?>>{{ trans('global.unpublished') }}</option>
										<option value="1"<?php if ($page->state == 1) { echo ' selected="selected"'; } ?>>{{ trans('global.published') }}</option>
									</select>
								</div>

								<div class="form-group col-md-6">
									<label class="form-label" for="field-publish_up">{{ trans('pages::pages.publish up') }}:</label>
									<input type="text" name="publish_up" id="field-publish_up" class="form-control datetime date-pick" value="<?php echo ($page->publish_up ? $page->publish_up->toDateTimeString() : $page->created->toDateTimeString()); ?>" />
								</div>

								<div class="form-group col-md-6">
									<label class="form-label" for="field-publish_down">{{ trans('pages::pages.publish down') }}:</label>
									<input type="text" name="publish_down" id="field-publish_down" class="form-control datetime date-pick" value="<?php echo ($page->publish_down ? $page->publish_down->toDateTimeString() : ''); ?>" placeholder="<?php echo ($page->publish_down ? '' : trans('global.never')); ?>" />
								</div>
							</div>
						@endif

						<input type="hidden" name="id" value="{{ $page->id }}" />
						@csrf
					</div>
					<div class="modal-footer">
						<button class="btn btn-success" id="save-page" type="submit">
							{{ trans('global.save') }}
							<span class="spinner-border spinner-border-sm" role="status"><span class="sr-only visually-hidden">{{ trans('global.saving') }}</span></span>
						</button>
						<?php /*<a href="{{ route('page', ['uri' => $page->path]) }}" data-id="{{ $page->id }}" class="cancel btn btn-link">{{ trans('global.button.cancel') }}</a>*/ ?>
					</div>
				</form>
			</div>
		</div>
		@endif
		@push('scripts')
			<script src="{{ timestamped_asset('modules/pages/js/site.js') }}"></script>
		@endpush
	@endif
@stop
