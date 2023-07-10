@extends('layouts.master')

@if ($node->page->metadesc || $node->page->metakey)
@section('meta')
	@if ($node->page->metadesc)
		<meta name="description" content="{{ $node->page->metadesc }}" />
	@endif
	@if ($node->page->metakey)
		<meta name="keywords" content="{{ $node->page->metakey }}" />
	@endif
@stop
@endif

@if ($node->page->metadata)
	@foreach ($node->page->metadata->all() as $k => $v)
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

@if ($node->isArchived())
	@push('meta')
		<meta name="robots" content="noindex, nofollow" />
	@endpush
@endif

@section('title'){{ trans('knowledge::knowledge.module name') }}: {{ ($node->guide ? $node->guide . ': ' : '') . $node->page->headline . ($all ? ': ' . trans('knowledge::knowledge.all topics') : '') }}@stop

@push('styles')
@if (auth()->user() && (auth()->user()->can('create knowledge') || auth()->user()->can('edit knowledge')))
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/core/vendor/tom-select/css/tom-select.bootstrap4.min.css') }}" />
@endif
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/core/vendor/prism/prism.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/knowledge/css/knowledge.css') }}" />
@endpush

@push('scripts')
@if (auth()->user() && (auth()->user()->can('create knowledge') || auth()->user()->can('edit knowledge')))
<script src="{{ timestamped_asset('modules/core/vendor/tom-select/js/tom-select.complete.min.js') }}"></script>
@endif
<script src="{{ timestamped_asset('modules/core/vendor/prism/prism.js') }}"></script>
<script src="{{ timestamped_asset('modules/knowledge/js/site.js') }}"></script>
@endpush

@section('content')
<div class="row">
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	@php
	$children = $root->publishedChildren();

	$p = implode('/', $path);
	$page = $node->page;
	@endphp

	@include('knowledge::site.list', ['nodes' => $children, 'path' => '', 'current' => $path, 'variables' => $root->page->variables->toArray()])
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<div class="row">
		<div class="col-md-9">
			<form method="get" action="{{ route('site.knowledge.search') }}">
				<div class="form-group">
					<label class="sr-only" for="knowledge_search">{{ trans('knowledge::knowledge.search') }}</label>
					<span class="input-group">
						<input type="search" enterkeyhint="search" name="search" id="knowledge_search" class="form-control" placeholder="{{ trans('knowledge::knowledge.search placeholder') }}" value="" />
						<span class="input-group-append">
							<input type="submit" class="input-group-text" value="{{ trans('global.submit') }}" />
						</span>
					</span>
					<input type="hidden" name="parent" value="{{ $parent }}" />
				</div>
			</form>
		</div>
		<div class="col-md-3 text-right">
		@if ($p)
			@if ($all)
				<a class="btn btn-secondary" href="<?php if ($p) { echo route('site.knowledge.page', ['uri' => $p]); } else { echo route('site.knowledge.index'); } ?>">{{ trans('knowledge::knowledge.collapse topics') }}</a>
			@else
				<a class="btn btn-secondary" href="<?php if ($p) { echo route('site.knowledge.page', ['uri' => $p, 'all' => 'true']); } else { echo route('site.knowledge.index', ['all' => 'true']); } ?>">{{ trans('knowledge::knowledge.expand topics') }}</a>
			@endif
		@endif
		</div>
	</div>

	<div class="warticle-wrap" id="page-content{{ $page->id }}">

		@if (auth()->user() && (auth()->user()->can('create knowledge') || auth()->user()->can('edit knowledge')))
		<div class="edit-controls">
			<div class="dropdown btn-group">
				<button class="btn ropdown-toggle" type="button" id="optionsbutton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<span class="fa fa-ellipsis-v" aria-hidden="true"></span><span class="sr-only"> {{ trans('knowledge::knowledge.options') }}</span>
				</button>
				<div class="dropdown-menu dropdown-menu-right" aria-labelledby="optionsbutton">
					@if (auth()->user()->can('create knowledge'))
						<a href="#new-page" data-toggle="modal" data-id="{{ $page->id }}" id="add-page" class="dropdown-item tip" title="{{ trans('knowledge::knowledge.add child page') }}">
							<span class="fa fa-plus" aria-hidden="true"></span> {{ trans('global.button.add') }}
						</a>
					@endif
					@if (auth()->user()->can('edit knowledge'))
						<a href="#page-form{{ $page->id }}" data-id="{{ $page->id }}" class="edit dropdown-item tip" title="{{ trans('global.button.edit') }}">
							<span class="fa fa-pencil" aria-hidden="true"></span> {{ trans('global.button.edit') }}
						</a>
					@endif
					@if (auth()->user()->can('delete knowledge'))
						<a href="{{ route('site.knowledge.delete', ['id' => $node->id]) }}" data-id="{{ $page->id }}" class="delete dropdown-item tip" data-confirm="{{ trans('global.confirm delete') }}" data-api="{{ route('api.knowledge.delete', ['id' => $node->id]) }}" title="{{ trans('global.button.delete') }}">
							<span class="fa fa-trash" aria-hidden="true"></span> {{ trans('global.button.delete') }}
						</a>
					@endif
				</div>
			</div>
		</div>
		@endif

		<article>
			@if ($node->isArchived())
				<div class="alert alert-warning">
					{{ trans('knowledge::knowledge.page is archived') }}
				</div>
			@endif

			@if ($page->params->get('show_title', 1))
				<h2>{{ $page->headline }}</h2>
			@endif

			@if ($page->content)
				{!! $page->body !!}
			@endif

			@if (!$page->content || $page->params->get('show_toc', 1) || $all)
				@php
				$childs = $node->publishedChildren();
				@endphp
				@if (count($childs))
					@if ($all)
						@foreach ($childs as $n)
							@php
								$n->page->mergeVariables($page->variables->all());
								$pa = $p ? $p . '/' . $n->page->alias : $n->page->alias;
							@endphp
							<section id="{{ str_replace('/', '_', $pa) }}">
								@if ($n->page->params->get('show_title', 1))
									<h2 id="{{ $n->id . '-' . $n->page->alias }}">{{ $n->page->headline }}</h2>
								@endif

								{!! $n->page->body !!}
							</section>
							@include('knowledge::site.articles', ['nodes' => $n->publishedChildren(), 'path' => $pa, 'variables' => $n->page->variables->all()])
						@endforeach
					@else
						<ul class="kb-toc">
						@foreach ($childs as $n)
							@php
								$n->page->mergeVariables($page->variables);
								$pa = $p ? $p . '/' . $n->page->alias : $n->page->alias;
							@endphp
							<li>
								<a href="{{ route('site.knowledge.page', ['uri' => $pa]) }}">{{ $n->page->headline }}</a>
								@if ($n->page->params->get('expandtoc'))
									@include('knowledge::site.list', ['nodes' => $n->publishedChildren(), 'path' => $pa, 'current' => ['__all__'], 'variables' => $n->page->variables->all()])
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
								<span id="okapi-a">
									<span>{{ trans('knowledge::knowledge.helpful') }}</span>
								</span>
							</legend>
							<div class="helpful-btn-grp">
								<button class="btn btn-feedback btn-outline-secondary" id="yes-button"
									data-feedback-type="positive"
									data-feedback-text="{{ trans('global.yes') }}"
									data-feedback-event="btn.click"
									title="{{ trans('knowledge::knowledge.answered my questions') }}">
									{{ trans('global.yes') }}
								</button>
								<button class="btn btn-feedback btn-outline-secondary" id="no-button"
									data-feedback-type="negative"
									data-feedback-text="{{ trans('global.no') }}"
									data-feedback-event="btn.click"
									title="{{ trans('knowledge::knowledge.not helpful') }}">
									{{ trans('global.no') }}
								</button>
							</div>
						</fieldset>
					</div>

					<div id="feedback-state" class="hide">
						<form autocomplete="off" method="post" action="{{ route('site.knowledge.page', ['uri' => ($p ? $p : '/')]) }}" data-api="{{ route('api.knowledge.feedback.create') }}">
							<p id="feedback-response"
								data-no-label="{{ trans('knowledge::knowledge.thanks for letting us know') }}"
								data-yes-label="{{ trans('knowledge::knowledge.we are glad it helped') }}">
								{{ trans('knowledge::knowledge.thanks for letting us know') }}
							</p>

							<div class="form-group">
								<label id="feedback-label" for="feedback-text" data-no-label="{{ trans('knowledge::knowledge.thanks for letting us know') }}" data-yes-label="{{ trans('knowledge::knowledge.we are glad it helped') }}">{{ trans('knowledge::knowledge.how to make more helpful') }}</label>
								<textarea id="feedback-text" name="comments" rows="2" cols="45"
									class="form-control form-counter-textarea"
									data-no-label="{{ trans('knowledge::knowledge.how to make more helpful') }}"
									data-yes-label="{{ trans('knowledge::knowledge.anything else to add') }}"
									data-max-length="250"
									aria-describedby="char_limit_counter"></textarea>
								<span class="form-text text-muted">{{ trans('knowledge::knowledge.feedback desc') }}</span>
								<div class="form-textbox-counter" id="char_limit_counter">
									<span class="sr-only" id="char-limit-message">{{ trans('knowledge::knowledge.characters left') }}:</span>
									<span class="char-count text-muted hide">250</span>
								</div>
							</div>

							<div class="form-group hide">
								<label for="feedback-hpt">{{ trans('knowledge::knowledge.honeypot label') }}</label>
								<input type="text" name="hpt" id="feedback-hpt" value="" />
							</div>

							<input type="hidden" name="target_id" value="{{ $node->id }}" />
							<input type="hidden" name="type" id="feedback-type" value="" />
							<input type="hidden" name="user_id" value="{{ auth()->user() ? auth()->user()->id : 0 }}" />

							<div class="form-group">
								<button type="submit" class="btn btn-primary" id="submit-feedback">
									{{ trans('global.submit') }}
								</button>
							</div>

							@csrf
						</form>
					</div>

					<div id="rating-done" class="alert alert-success hide">
						{{ trans('knowledge::knowledge.thank you for feedback') }}
					</div>
				</div>
			</div>
		@endif

		@if (auth()->user() && auth()->user()->can('edit knowledge'))
		<div class="text-muted mt-3">
			@php
			$latest = $page->history()->orderBy('created_at', 'desc')->first();
			$by = '';
			if ($latest)
			{
				$by = $latest->user ? ' by ' . $latest->user->name : '';
			}
			@endphp
			<p class="last-update">Last updated: {{ $node->page->updated_at->format('F j, Y g:ia T') . $by }}. <a href="{{ route('site.knowledge.page', ['uri' => ($p ? $p : '/'), 'action' => 'history']) }}">View change history</a>.</p>
		</div>
		@endif
	</div>

	@if (auth()->user())
		@if (auth()->user()->can('edit knowledge'))
		<div class="hide" id="page-form{{ $page->id }}">
			<form action="{{ route('site.knowledge.page', ['uri' => ($p ? $p : '/')]) }}" data-api="{{ route('api.knowledge.update', ['id' => $node->id]) }}" method="post" name="pageform" id="pageform" class="editform">
				@if (auth()->user()->can('edit pages'))
					<fieldset>
						<legend>{{ trans('global.details') }}</legend>

						<div class="form-group">
							<label for="field-parent_id">{{ trans('knowledge::knowledge.parent') }}</label>
							<select name="parent_id" id="field-parent_id" class="form-control searchable-select">
							@if ($node->id && $node->isRoot())
								<option value="0">{{ trans('global.none') }}</option>
							@else
								<?php foreach (App\Modules\Knowledge\Models\Page::tree() as $pa): ?>
									<?php $selected = ($pa->id == $node->parent_id ? ' selected="selected"' : ''); ?>
									<option value="{{ $pa->id }}"<?php echo $selected; ?> data-path="/{{ $pa->path }}" data-indent="<?php echo str_repeat('|&mdash; ', $pa->level); ?>"><?php echo str_repeat('|&mdash; ', $pa->level) . e(Illuminate\Support\Str::limit($pa->title, 70)); ?></option>
								<?php endforeach; ?>
							@endif
							</select>
						</div>

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
							<label for="field-title">{{ trans('knowledge::knowledge.title') }} <span class="required">{{ trans('global.required') }}</span></label>
							<input type="text" name="title" id="field-title" class="form-control{{ $errors->has('page.title') ? ' is-invalid' : '' }}" required maxlength="250" value="{{ $page->title }}" />
							<span class="invalid-feedback">{{ trans('knowledge::knowledge.invalid.title') }}</span>
						</div>

						<div class="form-group">
							<label for="field-alias">{{ trans('knowledge::knowledge.path') }}</label>
							<div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend">
									<div class="input-group-text">{{ route('site.knowledge.index') }}<span id="parent-path">{{ $parentpath }}</span>/</div>
								</div>
								<input type="text" name="alias" id="field-alias" class="form-control" maxlength="250"<?php if ($page->alias == 'home'): ?> disabled="disabled"<?php endif; ?> value="{{ $page->alias }}" />
							</div>
							<span class="form-text text-muted hint">{{ trans('knowledge::knowledge.path hint') }}</span>
						</div>

						<div class="form-group">
							<a href="#var-help" data-toggle="modal" class="float-right">
								Content Helpers
							</a>
							<label for="field-content">{{ trans('pages::pages.content') }} <span class="required">{{ trans('global.required') }}</span></label>
							{!! editor('content', $page->content, ['rows' => 35, 'class' => 'required', 'id' => 'field-content']) !!}
						</div>
					</fieldset>
				@endif

				<div class="row">
				@if (auth()->user()->can('edit.state pages'))
					<div class="col col-md-6">
						<fieldset>
							<legend>{{ trans('global.publishing') }}</legend>

							<div class="form-group">
								<label for="field-access">{{ trans('knowledge::knowledge.access') }}</label>
								<select class="form-control" name="access" id="field-access"<?php if ($page->isRoot()) { echo ' readonly="readonly" disabled="disabled"'; } ?>>
									@foreach (App\Halcyon\Access\Viewlevel::all() as $access)
										<option value="{{ $access->id }}"<?php if ($node->access == $access->id) { echo ' selected="selected"'; } ?>>{{ $access->title }}</option>
									@endforeach
								</select>
							</div>

							<div class="form-group">
								<label for="field-state">{{ trans('knowledge::knowledge.state') }}</label><br />
								<select class="form-control" name="state" id="field-state"<?php if ($page->isRoot()) { echo ' readonly="readonly" disabled="disabled"'; } ?>>
									<option value="0"<?php if ($node->state == 0) { echo ' selected="selected"'; } ?>>{{ trans('global.unpublished') }}</option>
									<option value="2"<?php if ($node->state == 2) { echo ' selected="selected"'; } ?>>&nbsp;|_&nbsp;{{ trans('knowledge::knowledge.archived') }}</option>
									<option value="1"<?php if ($node->state == 1) { echo ' selected="selected"'; } ?>>{{ trans('global.published') }}</option>
								</select>
							</div>
						</fieldset>
					</div>
					<div class="col col-md-6">
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
					<div class="col col-md-12">
						<fieldset>
							<legend>{{ trans('knowledge::knowledge.metadata') }}</legend>

							<div class="form-group">
								<label for="field-metakey">{{ trans('knowledge::knowledge.metakey') }}</label>
								<input type="text" name="metakey" id="field-metakey" class="form-control taggable" data-api="{{ route('api.tags.index') }}" value="{{ implode(', ', $page->tags->pluck('name')->toArray()) }}" />
							</div>

							<div class="form-group">
								<label for="field-metadesc">{{ trans('knowledge::knowledge.metadesc') }}</label>
								<textarea class="form-control" name="metadesc" id="field-metadesc" rows="3" cols="40">{{ $page->metadesc }}</textarea>
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
						<span class="spinner-border spinner-border-sm" role="status"><span class="sr-only">{{ trans('global.saving') }}</span></span>
					</button>
					<a href="{{ route('site.knowledge.page', ['uri' => ($p ? $p : '/')]) }}" data-id="{{ $page->id }}" class="cancel btn btn-link">{{ trans('global.button.cancel') }}</a>
				</p>
			</form>
		</div>
		<div class="modal" id="var-help" tabindex="-1" aria-labelledby="var-help-title" aria-hidden="true">
			<div class="modal-dialog modal-lg modal-dialog-centered">
				<div class="modal-content shadow-sm">
					<div class="modal-header">
						<div class="modal-title" id="var-help-title">Content Helpers</div>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div id="markdown-help-tabs" class="tabs">
							<ul class="nav nav-tabs mb-3" id="help1" role="tablist">
								<li class="nav-item" role="presentation"><a class="nav-link active" href="#help1a" data-toggle="tab" role="tab" id="help1-tab-1" aria-controls="help1a" aria-selected="true">If Statements</a></li>
								<li class="nav-item" role="presentation"><a class="nav-link" href="#help1b" data-toggle="tab" role="tab" id="help1-tab-2" aria-controls="help1b" aria-selected="false">Variable Usage</a></li>
								<li class="nav-item" role="presentation"><a class="nav-link" href="#help1c" data-toggle="tab" role="tab" id="help1-tab-3" aria-controls="help1c" aria-selected="false">Available Variables</a></li>
							</ul>
							<div class="tab-content" id="help1-content">
								<div class="tab-pane fade show active" role="tabpanel" aria-labelledby="help1-tab-1" id="help1a">
									<?php
$help1a = '<p>Pages may contain basic <code>if</code> statements to display content programmatically.</p>

{::if resource.name == Example}
<p>This one is an example.</p>
{::/}

<p>Values should <strong>not</strong> be quoted and available evaluations are: <code>==</code>, <code>!=</code>, <code>&gt;</code>, <code>&gt;=</code>, <code>&lt;</code>, <code>&lt;=</code>, <code>=~</code>.</p>

{::if resource.name == Example}
<p>This one is still example.</p>
{::elseif resource.name == Other}
<p>This one is other.</p>
{::/}

{::if resource.name == Example}
<p style="color:red;">Red</p>
{::else}
<p style="color:blue;">Blue</p>
{::/}
';

$help1b = '<p>Here, we can output variables such as the resource name. This is useful for re-usable pages (snippets) and injecting user names into examples. Variables look like: <code>$&#123;resource.name}</code>.</p>
<p>The name is ${resource.name} and your username is ${user.username}</p>';
									$article = new App\Modules\Knowledge\Models\Page(['content' => $help1a, 'params' => '{"variables":{"name":"Example"}}']);
									$article->params->set('variables', ['name' => 'Example']);
									?>
									<div class="form-group">
										<label for="help1ainput">Input text:</label>
										<textarea id="help1ainput" class="form-control samplebox" rows="5" data-sample="a"><?php echo $help1a; ?></textarea>
									</div>
									<p>Output text:<p>
									<div id="help1aoutput" class="sampleoutput">{!! $article->body !!}</div>
								</div>
								<div class="tab-pane fade" role="tabpanel" aria-labelledby="help1-tab-2" id="help1b">
									<?php
									$article = new App\Modules\Knowledge\Models\Page(['content' => $help1b, 'params' => '{"variables":{"name":"Example"}}']);
									$article->params->set('variables', ['name' => 'Example']);
									?>
									<div class="form-group">
										<label for="help1binput">Input text:</label>
										<textarea id="help1binput" class="form-control samplebox" rows="5" data-sample="b"><?php echo $help1b; ?></textarea>
									</div>
									<p>Output text:</p>
									<div id="help1boutput" class="sampleoutput">{!! $article->body !!}</div>
								</div>
								<div class="tab-pane fade" role="tabpanel" aria-labelledby="help1-tab-3" id="help1c">
									<table>
										<caption>Variables</caption>
										<thead>
											<tr>
												<th scope="col">Variable</th>
												<th scope="col">Value</th>
											</tr>
										</thead>
										<tbody>
											@foreach ($page->variables->all() as $k => $v)
												@if (is_array($v))
													@foreach ($v as $kk => $vv)
														@php
														if (is_array($vv)):
															$vv = $vv[0];
														endif;
														@endphp
														<tr>
															<td>${<?php echo $k . '.' . $kk; ?>}</td>
															<td>{{ $vv }}</td>
														</tr>
													@endforeach
												@else
												<tr>
													<td>${<?php echo $k; ?>}</td>
													<td>{{ $v }}</td>
												</tr>
												@endif
											@endforeach
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		@endif
		@if (auth()->user()->can('create knowledge'))
		<div class="modal dialog" id="new-page" tabindex="-1" aria-labelledby="new-page-title" aria-hidden="true" title="{{ trans('knowledge::knowledge.choose type') }}">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content dialog-content shadow-sm">
					<div class="modal-header">
						<div class="modal-title" id="new-page-title">{{ trans('knowledge::knowledge.choose type') }}</div>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body dialog-body">
						<div class="row">
							<div class="col-md-6">
								<a href="{{ route('site.knowledge.create', ['parent' => $node->id]) }}" class="form-group form-block text-center">
									<span class="fa fa-edit" aria-hidden="true"></span>
									{{ trans('knowledge::knowledge.new page') }}
								</a>
							</div>
							<div class="col-md-6">
								<a href="{{ route('site.knowledge.select', ['parent' => $node->id]) }}" class="form-group form-block text-center">
									<span class="fa fa-repeat" aria-hidden="true"></span>
									{{ trans('knowledge::knowledge.snippet') }}
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		@endif
	@endif
</div>
</div>
@stop