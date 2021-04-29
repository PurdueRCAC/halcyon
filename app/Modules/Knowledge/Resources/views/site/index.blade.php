@extends('layouts.master')

@section('title'){{ trans('knowledge::knowledge.module name') }}: {{ $node->page->headline }}@stop

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/prism/prism.css') }}?v={{ filemtime(public_path('modules/core/vendor/prism/prism.css')) }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/knowledge/css/knowledge.css') }}?v={{ filemtime(public_path('modules/knowledge/css/knowledge.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/prism/prism.js?v=' . filemtime(public_path() . '/modules/core/vendor/prism/prism.js')) }}"></script>
<script src="{{ asset('modules/knowledge/js/site.js?v=' . filemtime(public_path() . '/modules/knowledge/js/site.js')) }}"></script>
@endpush

@section('content')
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	@php
	$children = $root->publishedChildren();

	$p = implode('/', $path);
	$page = $node->page;
	@endphp
	@include('knowledge::site.list', ['nodes' => $children, 'path' => '', 'current' => $path, 'variables' => $root->page->variables])
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<div class="row">
		<div class="col-md-9">
			<form method="get" action="{{ route('site.knowledge.search') }}">
				<div class="form-group">
					<label class="sr-only" for="knowledge_search">{{ trans('knowledge::knowledge.search') }}</label>
					<span class="input-group">
						<input type="search" name="search" id="knowledge_search" class="form-control" placeholder="{{ trans('knowledge::knowledge.search placeholder') }}" value="" />
						<span class="input-group-append">
							<input type="submit" class="input-group-text" value="Submit" />
						</span>
					</span>
					<input type="hidden" name="parent" value="{{ $parent }}" />
				</div>
			</form>
		</div>
		<div class="col-md-3 text-right">
		@if ($p)
			@if (request('all'))
				<a class="btn btn-secondary" href="<?php if ($p) { echo route('site.knowledge.page', ['uri' => $p]); } else { echo route('site.knowledge.index'); } ?>">{{ trans('knowledge::knowledge.collapse topics') }}</a>
			@else
				<a class="btn btn-secondary" href="<?php if ($p) { echo route('site.knowledge.page', ['uri' => $p, 'all' => 'true']); } else { echo route('site.knowledge.index', ['all' => 'true']); } ?>">{{ trans('knowledge::knowledge.expand topics') }}</a>
			@endif
		@endif
		</div>
	</div>

	<div class="warticle-wrap" id="page-content{{ $page->id }}">

		@if (auth()->user() && auth()->user()->can('edit knowledge'))
		<div class="edit-controls">
			<a href="#page-form{{ $page->id }}" data-id="{{ $page->id }}" class="edit btn tip" title="{{ trans('global.button.edit') }}">
				<i class="fa fa-pencil" aria-hidden="true"></i><span class="sr-only">{{ trans('global.button.edit') }}</span>
			</a>
		</div>
		@endif

		<article>
			@if ($page->params->get('show_title', 1))
				<h2>{{ $page->headline }}</h2>
			@endif

			@if ($page->content)
				{!! $page->body !!}
			@endif

			@if (!$page->content || $page->params->get('show_toc', 1) || request('all'))
				@php
				$childs = $node->publishedChildren();
				@endphp
				@if (count($childs))
					@if (request('all'))
						@foreach ($childs as $n)
							@php
								$n->page->variables->merge($page->variables);
								$pa = $p ? $p . '/' . $n->page->alias : $n->page->alias;
							@endphp
							<section id="{{ str_replace('/', '_', $pa) }}">
								@if ($n->page->params->get('show_title', 1))
									<h3>{{ $n->page->headline }}</h3>
								@endif

								{!! $n->page->body !!}
							</section>
							@include('knowledge::site.articles', ['nodes' => $n->publishedChildren(), 'path' => $pa, 'variables' => $n->page->variables])
						@endforeach
					@else
						<ul class="kb-toc">
						@foreach ($childs as $n)
							@php
								$n->page->variables->merge($page->variables);
								$pa = $p ? $p . '/' . $n->page->alias : $n->page->alias;
							@endphp
							<li>
								<a href="{{ route('site.knowledge.page', ['uri' => $pa]) }}">{{ $n->page->headline }}</a>
								@if ($n->page->params->get('expandtoc'))
									@include('knowledge::site.list', ['nodes' => $n->publishedChildren(), 'path' => $pa, 'current' => ['__all__'], 'variables' => $n->page->variables])
								@endif
							</li>
						@endforeach
						</ul>
					@endif
				@endif
			@endif
		</article>

		@if (config('module.knowledge.collect_feedback', true))
			<div id="helpful" class="ratings card">
				<div class="card-body">
					<div id="question-state" class="show">
						<fieldset>
							<legend>
								<div id="okapi-a">
									<span>Helpful?</span>
								</div>
							</legend>
							<div class="helpful-btn-grp">
								<button class="btn btn-feedback btn-outline-secondary" id="yes-button"
									data-feedback-type="positive"
									data-feedback-text="yes"
									data-feedback-event="btn.click"
									title="Solved my problem">
									Yes
								</button>
								<button class="btn btn-feedback btn-outline-secondary" id="no-button"
									data-feedback-type="negative"
									data-feedback-text="no"
									data-feedback-event="btn.click"
									title="Not helpful">
									No
								</button>
							</div>
						</fieldset>
					</div>

					<div id="feedback-state" class="hide">
						<form autocomplete="off" method="post" action="{{ route('site.knowledge.page', ['uri' => ($p ? $p : '/')]) }}" data-api="{{ route('api.knowledge.feedback.create') }}">
							<p id="feedback-response"
								data-no-label="Thanks for letting us know."
								data-yes-label="We’re glad this article helped.">
								Thanks for letting us know.
							</p>

							<div class="form-group">
								<label id="feedback-label" for="feedback-text" data-no-label="Thanks for letting us know." data-yes-label="We’re glad this article helped.">How can we make this article more helpful? (Optional)</label>
								<textarea id="feedback-text" name="comments" rows="2" cols="45"
									class="form-control form-counter-textarea"
									data-no-label="How can we make this article more helpful? (Optional)"
									data-yes-label="Anything else you’d like us to know? (Optional)"
									data-max-length="250"
									aria-describedby="char_limit_counter"></textarea>
								<span class="form-text text-muted">Please don’t include any personal information in your comment. Maximum character limit is 250.</span>
								<div class="form-textbox-counter" id="char_limit_counter">
									<span class="sr-only" id="char-limit-message">Characters left:</span>
									<span class="char-count text-muted hide">250</span>
								</div>
							</div>

							<div class="form-group hide">
								<label for="feedback-hpt">Leave this field blank</label>
								<input type="text" name="hpt" id="feedback-hpt" value="" />
							</div>

							<input type="hidden" name="target_id" value="{{ $node->id }}" />
							<input type="hidden" name="type" id="feedback-type" value="" />
							<input type="hidden" name="user_id" value="{{ auth()->user() ? auth()->user()->id : 0 }}" />

							<div class="form-group">
								<button type="submit" class="btn btn-primary" id="submit-feedback">
									Submit
								</button>
							</div>

							@csrf
						</form>
					</div>

					<div id="rating-done" class="alert alert-success hide">
						Thanks for your feedback.
					</div>
				</div>
			</div>
		@endif
	</div>

	@if (auth()->user() && auth()->user()->can('edit knowledge'))
		<div class="hide" id="page-form{{ $page->id }}">
			<form action="{{ route('site.knowledge.page', ['uri' => ($p ? $p : '/')]) }}" data-api="{{ route('api.knowledge.update', ['id' => $node->id]) }}" method="post" name="pageform" id="pageform" class="editform">
				@if (auth()->user()->can('edit pages'))
					<fieldset>
						<legend>{{ trans('global.details') }}</legend>

						@if ($page->snippet)
							<div class="alert alert-warning">
								{{ trans('knowledge::knowledge.warning page is reusable') }}
							</div>
						@endif

						@php
						$parentpath = '';
						if ($page->path):
							if (trim($node->path, '/') != $page->alias):
								$parentpath = dirname($node->path);
								$parentpath = trim($parentpath, '/');
								$parentpath = $parentpath ? '/' . $parentpath : '';
							endif;
						endif;
						@endphp

						<div class="form-group">
							<label for="field-title">{{ trans('knowledge::knowledge.title') }}: <span class="required">{{ trans('global.required') }}</span></label>
							<input type="text" name="title" id="field-title" class="form-control{{ $errors->has('page.title') ? ' is-invalid' : '' }}" required maxlength="250" value="{{ $page->title }}" />
							<span class="invalid-feedback">{{ trans('knowledge::knowledge.invalid.title') }}</span>
						</div>

						<div class="form-group">
							<label for="field-alias">{{ trans('knowledge::knowledge.path') }}:</label>
							<div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend">
									<div class="input-group-text">{{ route('site.knowledge.index') }}<span id="parent-path">{{ $parentpath }}</span>/</div>
								</div>
								<input type="text" name="alias" id="field-alias" class="form-control" maxlength="250"<?php if ($page->alias == 'home'): ?> disabled="disabled"<?php endif; ?> value="{{ $page->alias }}" />
							</div>
							<span class="form-text text-muted hint">{{ trans('knowledge::knowledge.path hint') }}</span>
						</div>

						<div class="form-group">
							<label for="field-content">{{ trans('pages::pages.content') }}: <span class="required">{{ trans('global.required') }}</span></label>
							{!! editor('content', $page->content, ['rows' => 35, 'class' => 'required']) !!}
						</div>
					</fieldset>
				@endif

				<div class="row">
				@if (auth()->user()->can('edit.state pages'))
					<div class="col col-md-6">
						<fieldset>
							<legend>{{ trans('global.publishing') }}</legend>

							<div class="form-group">
								<label for="field-access">{{ trans('knowledge::knowledge.access') }}:</label>
								<select class="form-control" name="access" id="field-access"<?php if ($page->isRoot()) { echo ' readonly="readonly" disabled="disabled"'; } ?>>
									@foreach (App\Halcyon\Access\Viewlevel::all() as $access)
										<option value="{{ $access->id }}"<?php if ($node->access == $access->id) { echo ' selected="selected"'; } ?>>{{ $access->title }}</option>
									@endforeach
								</select>
							</div>

							<div class="form-group">
								<label for="field-state">{{ trans('knowledge::knowledge.state') }}:</label><br />
								<select class="form-control" name="state" id="field-state"<?php if ($page->isRoot()) { echo ' readonly="readonly" disabled="disabled"'; } ?>>
									<option value="0"<?php if ($node->state == 0) { echo ' selected="selected"'; } ?>>{{ trans('global.unpublished') }}</option>
									<option value="1"<?php if ($node->state == 1) { echo ' selected="selected"'; } ?>>{{ trans('global.published') }}</option>
								</select>
							</div>
						</fieldset>
					</div>
					<div class="col-md-6">
				@else
					<div class="col col-md-12">
				@endif
						<fieldset>
							<legend>{{ trans('knowledge::knowledge.options') }}</legend>

							<div class="form-group">
								<label for="params-show_title">{{ trans('knowledge::knowledge.show title') }}</label>
								<select name="params[show_title]" id="params-show_title" class="form-control">
									<option value="0"<?php if (!$page->params->get('show_title', 1)) { echo ' selected="selected"'; } ?>>{{ trans('global.no') }}</option>
									<option value="1"<?php if ($page->params->get('show_title', 1)) { echo ' selected="selected"'; } ?>>{{ trans('global.yes') }}</option>
								</select>
							</div>

							<div class="form-group">
								<label for="params-show_toc">{{ trans('knowledge::knowledge.show toc') }}</label>
								<select name="params[show_toc]" id="params-show_toc" class="form-control">
									<option value="0"<?php if (!$page->params->get('show_toc', 1)) { echo ' selected="selected"'; } ?>>{{ trans('global.no') }}</option>
									<option value="1"<?php if ($page->params->get('show_toc', 1)) { echo ' selected="selected"'; } ?>>{{ trans('global.yes') }}</option>
								</select>
							</div>
						</fieldset>
					</div>
				</div>

				<input type="hidden" name="id" value="{{ $node->id }}" />
				<input type="hidden" name="page_id" value="{{ $page->id }}" />
				<input type="hidden" name="snippet" value="{{ $page->snippet }}" />

				@csrf

				<p class="text-center">
					<button class="btn btn-success" id="save-page" type="submit">
						{{ trans('global.save') }}
						<span class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Saving...</span></span>
					</button>
					<a href="{{ route('site.knowledge.page', ['uri' => ($p ? $p : '/')]) }}" data-id="{{ $page->id }}" class="cancel btn btn-link">Cancel</a>
				</p>
			</form>
		</div>
	@endif
</div>
@stop