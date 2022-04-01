@extends('layouts.master')

@section('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css') }}" />
@stop

@section('scripts')
<script src="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.js?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.js')) }}"></script>
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
<script src="{{ asset('modules/issues/js/admin.js?v=' . filemtime(public_path() . '/modules/issues/js/admin.js')) }}"></script>
@stop

@php
app('pathway')
	->append(
		trans('issues::issues.module name'),
		route('admin.issues.index')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('delete issues'))
		{!! Toolbar::deleteList('', route('admin.issues.delete')) !!}
	@endif

	@if (auth()->user()->can('create issues'))
		{!! Toolbar::addNew(route('admin.issues.create')) !!}
	@endif

	@if (auth()->user()->can('admin issues'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('issues')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('issues::issues.module name') }}
@stop

@section('panel')
	<div class="row mb-4">
		<div class="col-md-6">
			<div class="card-title">{{ trans('issues::issues.checklist') }}</div>
		</div>
		<div class="col-md-6 text-right">
			<label for="checklist_status" class="sr-only">{{ trans('issues::issues.show') }}</label>
			<select name="checklist_status" id="checklist_status" class="form-control form-control-sm">
				<option value="all">{{ trans('issues::issues.all') }}</option>
				<option value="incomplete" selected="selected">{{ trans('issues::issues.incomplete') }}</option>
				<option value="complete">{{ trans('issues::issues.complete') }}</option>
			</select>
		</div>
	</div>

	@if (count($todos))
		<ul class="list-group list-group-flush checklist">
			@foreach ($todos as $todo)
				<li class="list-group-item pl-0 pr-0 {{ $todo->status == 'complete' ? 'hide complete' : 'incomplete' }}">
					<div class="d-flex w-100 justify-content-between">
						<div class="form-group float-lef">
							<div class="form-check">
								<input type="checkbox"
									class="form-check-input issue-todo"
									data-name="{{ $todo->name }}"
									data-id="{{ $todo->id }}"
									data-api="{{ route('api.issues.create') }}"
									data-issue="{{ $todo->issue }}"
									name="todo{{ $todo->id }}"
									id="todo{{ $todo->id }}"
									value="1"
									{{ $todo->status == 'complete' ? 'checked="checked"' : '' }} />
								<label class="form-check-label" for="todo{{ $todo->id }}"><span class="sr-only">{{ trans('issues::issues.mark as complete') }}</span></label>
							</div>
						</div>
						<div>
							{{ $todo->name }}
							<span class="issue-todo-alert tip"><span class="fa" aria-hidden="true"></span></span>
							@if ($todo->description)
								<div class="text-muted">{!! $todo->formattedDescription !!}</div>
							@endif
						</div>
						<div>
							@php
							$badge = 'secondary';

							switch ($todo->timeperiod->name):
								case 'hourly':
									$badge = 'danger';
								break;

								case 'daily':
									$badge = 'warning';
								break;

								case 'weekly':
									$badge = 'info';
								break;
							endswitch;
							@endphp
							<span class="badge badge-{{ $badge }}">{{ $todo->timeperiod->name }}</span>
						</div>
					</div>
				</li>
			@endforeach
		</ul>
	@else
		<ul class="list-group checklist">
			<li class="list-group-item text-center">{{ trans('issues::issues.no todos found') }}</li>
		</ul>
	@endif
@stop

@section('content')
@component('issues::admin.submenu')
	issues
@endcomponent
<form action="{{ route('admin.issues.index') }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-4">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
			<div class="col col-md-2">
				@if ($filters['tag'])
				<span class="input-group">
					<span class="form-control">
						<span class="tag badge badge-secondary">
							{{ $filters['tag'] }}
							<a href="{{ route('admin.issues.index', ['tag' => '']) }}" class="fa fa-times">x</a>
						</span>
					</span>
					<span class="input-group-append">
						<span class="input-group-text">
							<span class="fa fa-tag" aria-hidden="true"></span>
							<span class="sr-only">{{ trans('issues::issues.tags') }}</span>
						</span>
					</span>
				</span>
				@endif
			</div>
			<div class="col col-md-3">
				<label class="sr-only" for="filter_start">{{ trans('issues::issues.start') }}</label>
				<span class="input-group">
					<input type="text" name="start" id="filter_start" class="form-control filter filter-submit date" size="10" value="{{ $filters['start'] }}" placeholder="{{ trans('issues::issues.start') }}" />
					<span class="input-group-append"><span class="input-group-text"><span class="icon-calendar" aria-hidden="true"></span></span>
				</span>
			</div>
			<div class="col col-md-3">
				<label class="sr-only" for="filter_stop">{{ trans('issues::issues.stop') }}</label>
				<span class="input-group">
					<input type="text" name="stop" id="filter_stop" class="form-control filter filter-submit date" size="10" value="{{ $filters['stop'] }}" placeholder="{{ trans('issues::issues.stop') }}" />
					<span class="input-group-append"><span class="input-group-text"><span class="icon-calendar" aria-hidden="true"></span></span></span>
				</span>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	@if (count($rows))
		@foreach ($rows as $i => $row)
			<div class="card mb-3">
				<div class="card-header">
					<div class="d-flex">
						@if (auth()->user()->can('delete issues'))
							<div>
								{!! Html::grid('id', $i, $row->id) !!}
							</div>
						@endif
						<div class="text-muted ml-4">
							<span class="fa fa-calendar" aria-hidden="true"></span>
							@if ($row->datetimecreated)
								<time datetime="{{ $row->datetimecreated->format('Y-m-dTh:i:s') }}">
									@if ($row->datetimecreated->format('Y-m-dTh:i:s') > Carbon\Carbon::now()->toDateTimeString())
										{{ $row->datetimecreated->diffForHumans() }}
									@else
										{{ $row->datetimecreated->format('M d, Y') }}
									@endif
								</time>
							@else
								<span class="never">{{ trans('global.unknown') }}</span>
							@endif
						</div>
						<div class="text-muted ml-4">
						@if ($row->creator)
							<span class="fa fa-user" aria-hidden="true"></span>
							{{ $row->creator->name }}
						@endif
						</div>
						<div class="flex-fill text-right">
							@if (auth()->user()->can('edit issues'))
								<a class="btn" href="{{ route('admin.issues.edit', ['id' => $row->id]) }}">
									<span class="fa fa-pencil" aria-hidden="true"></span>
									<span class="sr-only"># {{ $row->id }}</span>
								</a>
							@else
								# {{ $row->id }}
							@endif
						</div>
					</div>
				</div>
				<div class="card-body">
					{!! $row->formattedReport !!}
					@if (count($row->tags))
						<br />
						@foreach ($row->tags as $tag)
							<a class="tag badge badge-sm badge-secondary" href="{{ route('admin.issues.index', ['tag' => $tag->slug]) }}">{{ $tag->name }}</a>
						@endforeach
					@endif

					<div class="d-flex mt-4 text-muted">
						<div class="flex-fill">
							<?php
							$names = array();

							foreach ($row->resources as $res):
								if ($res->resource):
									$names[] = '<span class="badge badge-info">' . e($res->resource->name) . '</span>';
								else:
									$names[] = $res->resourceid;
								endif;
							endforeach;

							asort($names);

							if (count($names)):
								echo implode(' ', $names);
							endif;
							?>
						</div>
						<div class="flex-fill text-right">
							<span class="fa fa-comment" aria-hidden="true"></span>
							@if ($row->comments_count)
								<a href="#comments_{{ $row->id }}" class="comments-show">{{ $row->comments_count }}</a>
							@else
								<span class="none">{{ $row->comments_count }}</span>
							@endif
						</div>
					</div>
				</div>
			</div>

			<?php
			$comments = $row->comments()->orderBy('datetimecreated', 'asc')->get();

			if (count($comments) > 0):
				?>
				<div class="card ml-4 hide" id="comments_{{ $row->id }}">
					<ul class="list-group w-100">
						@foreach ($comments as $comment)
							<li id="comment_{{ $comment->id }}" class="list-group-item" data-api="{{ route('api.issues.comments.update', ['comment' => $comment->id]) }}">
								{!! $comment->formattedComment !!}

								<div class="d-flex text-muted">
									<div class="mr-4">
										<span class="fa fa-calendar" aria-hidden="true"></span>
										<time datetime="{{ $comment->datetimecreated->format('Y-m-dTh:i:s') }}">
											{{ $comment->datetimecreated->format('M d, Y') }} @ {{ $comment->datetimecreated->format('h:i a') }}
										</time>
									</div>
									<div class="mr-4">
										@if ($comment->creator)
											<span class="fa fa-user" aria-hidden="true"></span>
											{{ $comment->creator->name }}
										@endif
									</div>
									<div class="flex-fill text-right">
										<span class="badge badge-success<?php if (!$comment->resolution) { echo ' hide'; } ?>">{{ trans('issues::issues.resolution') }}</span>
									</div>
								</div>
							</li>
						@endforeach
					</ul>
				</div>
				<?php
			endif;
			?>
		@endforeach

		{{ $rows->render() }}
	@else
		<div class="card-body text-center">
			<div>{{ trans('global.no results') }}</div>
		</div>
	@endif

	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>
@stop
