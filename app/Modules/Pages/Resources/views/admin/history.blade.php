@extends('layouts.master')

@push('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/chartjs/Chart.min.js') }}"></script>
<script src="{{ timestamped_asset('modules/pages/js/pages.js') }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('pages::pages.module name'),
		route('admin.pages.index')
	)
	->append(
		trans('pages::pages.history') . ' #' . $row->id
	);
@endphp

@section('toolbar')
	{!! Toolbar::link('back', trans('pages::pages.back'), route('admin.pages.index'), false) !!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('pages::pages.module name') }}: {{ trans('pages::pages.history') . ' #' . $row->id }}
@stop

@section('content')
<form action="{{ route('admin.pages.index') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<div class="card mb-4">
		<table class="table table-hover adminlist">
			<caption>{{ $row->title }}</caption>
			<thead>
				<tr>
					<th scope="col" class="priority-3">
						{!! trans('pages::pages.actor') !!}
					</th>
					<th scope="col">
						{!! trans('pages::pages.action') !!}
					</th>
					<th scope="col">
						{!! trans('pages::pages.fields') !!}
					</th>
					<th scope="col">
						{!! trans('pages::pages.datetime') !!}
					</th>
				</tr>
			</thead>
			<tbody>
		@if (count($history))
			<?php
			$canEdit = auth()->user() && auth()->user()->can('edit pages');
			$formatter = new App\Modules\History\Helpers\Diff\Formatter\Table();
			?>
			@foreach ($history as $i => $action)
				<?php
				$actor = trans('global.unknown');

				if ($action->user):
					$actor = $action->user->name . ' (' . $action->user->username . ')';
				endif;

				$created = $action->created_at ? $action->created_at : trans('global.unknown');

				if (is_object($action->new)):
					$f = get_object_vars($action->new);
				elseif (is_array($action->new)):
					$f = $action->new;
				endif;

				$fields = array_keys($f);

				foreach ($fields as $z => $k):
					if (in_array($k, ['created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at'])):
						unset($fields[$z]);
					endif;
				endforeach;

				$badge = 'info';
				if ($action->action == 'created'):
					$badge = 'success';
				endif;
				if ($action->action == 'deleted'):
					$badge = 'danger';
				endif;

				$old = Carbon\Carbon::now()->subDays(2);
				?>
				<tr>
					<td class="priority-5">
						{{ $actor }}
					</td>
					<td class="priority-2">
						<span class="badge badge-{{ $badge }} entry-action">{{ $action->action }}</span>
					</td>
					<td class="priority-3">
						@if ($action->action == 'updated')
							<span class="entry-diff"><code><?php echo implode('</code>, <code>', $fields); ?></code></span>
						@endif
					</td>
					<td>
						<a href="#page-history{{ $action->id }}" data-toggle="modal" class="tip" title="View changes from previous version">
							<time datetime="{{ $action->created_at->toDateTimeLocalString() }}">
								@if ($action->created_at < $old)
									{{ $action->created_at->format('d M Y') }}
								@else
									{{ $action->created_at->diffForHumans() }}
								@endif
							</time>
						</a>
						<div id="page-history{{ $action->id }}" class="modal fade" tabindex="-1" aria-labelledby="page-history-title{{ $action->id }}" aria-hidden="true">
							<div class="modal-dialog modal-xl modal-dialog-centered">
								<div class="modal-content">
									<div class="modal-header">
										<h3 class="modal-title" id="page-history-title{{ $action->id }}">Changes</h3>
										<button type="button" class="close" data-dismiss="modal" aria-label="Close">
											<span aria-hidden="true">&times;</span>
										</button>
									</div>
									<div class="modal-body">
										<?php
										if (isset($action->new->title)):
											$ota = isset($action->old->title) ? [$action->old->title] : [];
											$nta = [$action->new->title];

											echo '<h3>Title</h3>';
											echo $formatter->format(new App\Modules\History\Helpers\Diff($ota, $nta));
										endif;

										if (isset($action->new->alias)):
											$ota = isset($action->old->alias) ? [$action->old->alias] : [];
											$nta = [$action->new->alias];

											echo '<h3>Page alias</h3>';
											echo $formatter->format(new App\Modules\History\Helpers\Diff($ota, $nta));
										endif;

										if (isset($action->new->content)):
											$ota = isset($action->old->content) ? explode("\n", $action->old->content) : [];
											$nta = explode("\n", $action->new->content);

											echo '<h3>Content</h3>';
											echo $formatter->format(new App\Modules\History\Helpers\Diff($ota, $nta));
										endif;

										if (isset($action->new->params)):
											$orparams = isset($action->old->params) ? (array)$action->old->params : [];
											$drparams = (array)$action->new->params;

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
						@if ($canEdit && $i > 0)
						<a href="{{ route('admin.pages.revert', ['id' => $row->id, 'revision' => $action->id]) }}" class="tip confirm-revert" title="Revert to this version" data-confirm="Are you sure you want to revert the page to this version?">
							<span class="fa fa-undo" aria-hidden="true"></span>
							<span class="sr-only visually-hidden">Revert to this version</span>
						</a>
						@endif
					</td>
				</tr>
			@endforeach
		@else
				<tr>
					<td class="priority-5">
						{{ $row->creator ? $row->creator->name : trans('global.unknown') }}
					</td>
					<td class="priority-2">
						<span class="badge badge-success entry-action">{{ trans('pages::pages.created') }}</span>
					</td>
					<td class="priority-3">
					</td>
					<td class="priority-4">
						<time datetime="{{ $row->created_at->toDateTimeLocalString() }}">
							@if ($row->created_at < Carbon\Carbon::now()->subDays(2))
								{{ $row->created_at->format('d M Y') }}
							@else
								{{ $row->created_at->diffForHumans() }}
							@endif
						</time>
					</td>
				</tr>
		@endif
			</tbody>
		</table>
	</div>

	@csrf
</form>

@stop