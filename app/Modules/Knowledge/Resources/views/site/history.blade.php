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

@section('title'){{ trans('knowledge::knowledge.module name') }}: {{ ($node->guide ? $node->guide . ': ' : '') . $node->page->headline . ($all ? ': ' . trans('knowledge::knowledge.all topics') : '') }}@stop

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

	@include('knowledge::site.list', ['nodes' => $children, 'path' => '', 'current' => $path, 'variables' => $root->page->variables->toArray()])
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	@if ($node->isArchived())
		<div class="alert alert-warning">
			{{ trans('knowledge::knowledge.page is archived') }}
		</div>
	@endif

	@if ($page->params->get('show_title', 1))
		<h2>{{ $page->headline }}</h2>
	@endif

	<table class="table" id="revisionhistory">
		<caption>Change History</caption>
		<thead>
			<tr>
				<th scope="col" colspan="2">Changed</th>
				<th scope="col">When</th>
				<th scope="col">Made by</th>
				<?php /*@if (auth()->user()->can('edit knowledge'))
					<th scope="col" class="text-center">Option</th>
				@endif*/ ?>
			</tr>
		</thead>
		<tbody>
			<?php
			$i = 0;

			$revisions = $page->history()
				->orderBy('created_at', 'desc')
				->paginate(20, ['*'], 'page', request()->input('page', 1));
				//->append(['action' => 'history']);

			$latest = $revisions->first();

			$formatter = new App\Modules\History\Helpers\Diff\Formatter\Table();

			foreach ($revisions as $revision):
				$i++;

				$actor = trans('global.unknown');

				if ($revision->user):
					$actor = $revision->user->name;
				endif;

				$created = $revision->created_at ? $revision->created_at : trans('global.unknown');

				if (is_object($revision->new)):
					$f = get_object_vars($revision->new);
				elseif (is_array($revision->new)):
					$f = $revision->new;
				endif;

				$fields = array_keys($f);
				foreach ($fields as $i => $k):
					if (in_array($k, ['created_at', 'updated_at', 'deleted_at'])):
						unset($fields[$i]);
					endif;
				endforeach;
				?>
				<tr>
					<td>
						@if (in_array('title', $fields))
							Title<br />
						@endif
						@if (in_array('alias', $fields))
							Page alias<br />
						@endif
						@if (in_array('content', $fields))
							Content<br />
						@endif
						@if (in_array('params', $fields))
							Page options
						@endif
					</td>
					<td>
						<a href="#page-history{{ $revision->id }}" data-toggle="modal">View</a>
						<div id="page-history{{ $revision->id }}" class="modal fade" tabindex="-1" aria-labelledby="page-history-title{{ $revision->id }}" aria-hidden="true">
							<div class="modal-dialog modal-lg modal-dialog-centered">
								<div class="modal-content">
									<div class="modal-header">
										<h3 class="modal-title" id="page-history-title{{ $revision->id }}">Changes</h3>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<div class="modal-body">
										<?php
										if (isset($revision->new->title)):
											$ota = isset($revision->old->title) ? [$revision->old->title] : [];
											$nta = [$revision->new->title];

											echo '<h3>Title</h3>';
											echo $formatter->format(new App\Modules\History\Helpers\Diff($ota, $nta));
										endif;

										if (isset($revision->new->alias)):
											$ota = isset($revision->old->alias) ? [$revision->old->alias] : [];
											$nta = [$revision->new->alias];

											echo '<h3>Page alias</h3>';
											echo $formatter->format(new App\Modules\History\Helpers\Diff($ota, $nta));
										endif;

										if (isset($revision->new->content)):
											$ota = isset($revision->old->content) ? explode("\n", $revision->old->content) : [];
											$nta = explode("\n", $revision->new->content);

											echo '<h3>Content</h3>';
											echo $formatter->format(new App\Modules\History\Helpers\Diff($ota, $nta));
										endif;

										if (isset($revision->new->params)):
											$orparams = isset($revision->old->params) ? (array)$revision->old->params : [];
											$drparams = (array)$revision->new->params;

											// Params
											$ota = [];
											$nta = [];
											foreach (['show_title', 'show_toc'] as $p):
												if (isset($orparams[$p]) || isset($drparams[$p])):
													$ota[] = isset($orparams[$p]) ? $p . ': ' . ($orparams[$p] ? 'true' : 'false') : '';
													$nta[] = isset($drparams[$p]) ? $p . ': ' . ($drparams[$p] ? 'true' : 'false') : '';
												endif;
											endforeach;

											if (!empty($ota) && !empty($nta)):
												echo '<h3>Options</h3>';
												echo $formatter->format(new App\Modules\History\Helpers\Diff($ota, $nta));
											endif;

											// Variables
											if (isset($orparams['variables']) || isset($drparams['variables'])):
												$ota = isset($orparams['variables']) ? $orparams['variables'] : [];
												$nta = isset($drparams['variables']) ? $drparams['variables'] : [];

												if ($ota != $nta):
													echo '<h3>Variables</h3>';
													echo $formatter->format(new App\Modules\History\Helpers\Diff($ota, $nta));
												endif;
											endif;

											// Tags
											if (isset($orparams['tags']) || isset($drparams['tags'])):
												$ota = isset($orparams['tags']) ? $orparams['tags'] : [];
												$nta = isset($drparams['tags']) ? $drparams['tags'] : [];

												if ($ota != $nta):
													echo '<h3>Tags</h3>';
													echo $formatter->format(new App\Modules\History\Helpers\Diff($ota, $nta));
												endif;
											endif;
										endif;
										?>
									</div>
								</div>
							</div>
						</div>
					</td>
					<td>
						<time datetime="{{ $revision->created_at->toDateTimeString() }}">{{ $revision->created_at->toDateTimeString() }}</time>
					</td>
					<td>
						{{ $actor }}
					</td>
					<?php /*<td class="text-center">
						@if (auth()->user()->can('edit knowledge'))
							@if ($latest->id == $revision->id)
								(current)
							@else
							<form method="post" action="{{ route('site.knowledge.restore') }}" data-action="{{ route('site.knowledge.page', ['uri' => $node->path]) }}">
								<button type="submit" class="btn btn-restore" data-id="{{ $revision->id }}" data-confirm="Are you sure you want to restore to this version?" title="Restore to this version">
									<span class="fa fa-undo" aria-hidden="true"></span>
									Restore
								</button>
								<input type="hidden" name="node" value="{{ $node->id }}" />
								<input type="hidden" name="revision" value="{{ $revision->id }}" />
								@csrf
							</form>
							@endif
						@endif
					</td>*/ ?>
				</tr>
				<?php
			endforeach;
			?>
		</tbody>
	</table>

	{{ $revisions->render() }}
</div>
@stop
