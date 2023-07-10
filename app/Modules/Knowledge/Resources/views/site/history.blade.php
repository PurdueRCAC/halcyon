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
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/core/vendor/prism/prism.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/knowledge/css/knowledge.css') }}" />
@endpush

@push('scripts')
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
	@if ($node->isArchived())
		<div class="alert alert-warning">
			{{ trans('knowledge::knowledge.page is archived') }}
		</div>
	@endif

	@if ($page->params->get('show_title', 1))
		<h2>{{ $page->headline }}</h2>
	@endif

	<form method="POST" action="{{ route('site.knowledge.page', ['uri' => ($p ? $p : '/'), 'action' => 'history']) }}">

	<table class="table" id="revisionhistory">
		<caption>Change History</caption>
		<thead>
			<tr>
				<th scope="col" colspan="2">
					<a class="btn btn-sm btn-secondary btn-diff" href="#page-history" data-toggle="modal" data-api="{{ route('api.knowledge.diff') }}" data-emptydiff="No diffable values found between the revisions.">Compare</a>
				</th>
				<th scope="col">When</th>
				<th scope="col">Changed</th>
				<th scope="col">Length</th>
				<th scope="col">Made by</th>
			</tr>
		</thead>
		<tbody>
			<?php
			$i = 0;
			$comparefirst = true;

			$revisions = $page->history()
				->orderBy('created_at', 'desc')
				->paginate(20, ['*'], 'page', request()->input('page', 1))
				->appends(['action' => 'history']);

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
				foreach ($fields as $j => $k):
					if (in_array($k, ['created_at', 'updated_at', 'deleted_at'])):
						unset($fields[$j]);
					endif;
				endforeach;
				?>
				<tr id="page-revision-{{ $revision->id }}">
					@if ($i == 1)
						<td>

						</td>
						<td>
							<input type="radio" name="newid" value="{{ $revision->id }}" class="page-revision-id page-revision-newid" checked="checked" />
						</td>
					@else
						<td>
							<input type="radio" name="oldid" value="{{ $revision->id }}" class="page-revision-id page-revision-oldid"<?php
							if ($comparefirst == true)
							{
								echo ' checked="checked"';
								$comparefirst = false;
							} ?> />
						</td>
						<td>
							<input type="radio" name="newid" value="{{ $revision->id }}" class="page-revision-id page-revision-newid d-none" />
						</td>
					@endif
					<td>
						<a href="#page-history{{ $revision->id }}" data-toggle="modal" class="tip" title="View changes from previous version">
							<time datetime="{{ $revision->created_at->toDateTimeString() }}">{{ $revision->created_at->toDateTimeString() }}</time>
						</a>
						<div id="page-history{{ $revision->id }}" class="modal fade" tabindex="-1" aria-labelledby="page-history-title{{ $revision->id }}" aria-hidden="true">
							<div class="modal-dialog modal-xl modal-dialog-centered">
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
													$otaa = array();
													foreach ($ota as $k => $v)
													{
														$otaa[] = $k . ': ' . $v;
													}
													$ntaa = array();
													foreach ($nta as $k => $v)
													{
														$ntaa[] = $k . ': ' . $v;
													}
													echo $formatter->format(new App\Modules\History\Helpers\Diff($otaa, $ntaa));
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
						<?php
						if (in_array('content', $fields)):
							$l = strlen($revision->new->content);
							$changed = $l - (isset($revision->old->content) ? strlen($revision->old->content) : 0);
							?>
							{{ number_format(abs($l)) }} bytes
							@if ($changed > 0)
								(<span class="text-success">+{{ number_format(abs($changed)) }}</span>)
							@elseif ($changed < 0)
								(<span class="text-danger">-{{ number_format(abs($changed)) }}</span>)
							@endif
							<?php
						endif;
						?>
					</td>
					<td>
						{{ $actor }}
					</td>
				</tr>
				<?php
			endforeach;
			?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="2">
					<a class="btn btn-sm btn-secondary btn-diff" href="#page-history" data-toggle="modal" data-api="{{ route('api.knowledge.diff') }}" data-emptydiff="No diffable values found between the revisions.">Compare</a>
				</td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
		</tfoot>
	</table>
	@csrf

	{{ $revisions->render() }}

	<div id="page-history" class="modal fade" tabindex="-1" aria-labelledby="page-history-title" aria-hidden="true">
		<div class="modal-dialog modal-xl modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h3 class="modal-title" id="page-history-title">Changes</h3>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body" id="page-diff">
				</div>
			</div>
		</div>
	</div>

	</form>
</div>
</div>
@stop
