@extends('layouts.master')

@section('meta')
		<meta name="description" content="{{ trans('software::software.software catalog') }}" />
@stop

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/software/css/software.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/software/js/site.js') }}"></script>
@endpush

@section('title'){{ trans('software::software.software') . ($rows->total() > $filters['limit'] ? ': Page ' . $filters['page'] : '') }}@stop

@php
app('pathway')
	->append(
		trans('software::software.software'),
		route('site.software.index')
	);
@endphp

@section('content')
<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">

		@if (auth()->user() && auth()->user()->can('create software'))
			<div class="pull-right">
				<a href="{{ route('site.software.create') }}" class="btn btn-primary">
					<span class="fa fa-plus" aria-hidden="true"></span> {{ trans('global.create') }}
				</a>
			</div>
		@endif

		<h2 class="mt-0">{{ trans('software::software.software') }}</h2>

		<form action="{{ route('site.software.index') }}" method="get" class="row">
			<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
				<fieldset class="filters mt-0">
					<div class="form-group">
						<label for="filter_search">{{ trans('search.label') }}</label>
						<span class="input-group">
							<input type="search" enterkeyhint="search" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('software::software.search placeholder') }}" value="{{ $filters['search'] }}" />
							<span class="input-group-append">
								<button class="btn input-group-text" type="submit">
									{{ trans('software::software.filter') }}
								</button>
							</span>
						</span>
					</div>

					<div class="form-group">
						<label for="filter_type">{{ trans('software::software.type') }}</label>
						<ul class="na flex-column">
							@foreach ($types as $type)
								<li class="nav-ite<?php if ($filters['type'] == $type->alias) { echo ' active'; } ?>">
									<a class="nav-lin<?php if ($filters['type'] == $type->alias) { echo ' active'; } ?>" href="{{ route('site.software.index', ['type' => $type->alias]) }}">{{ $type->title }}</a>
								</li>
							@endforeach
						</ul>
					</div>

					<div class="form-group">
						<label for="filter_resource">{{ trans('software::software.resource') }}</label>

						<ul class="mb-0">
							@foreach ($resources as $resource)
								<li class="nav-ite<?php if ($filters['resource'] == $resource->id) { echo ' active'; } ?>">
									<a class="nav-lin<?php if ($filters['resource'] == $resource->id) { echo ' active'; } ?>" href="{{ route('site.software.index', ['resource' => $resource->id]) }}">{{ $resource->name }}</a>
								</li>
							@endforeach
						</ul>
					</div>

					<input type="hidden" name="filter_order" value="{{ $filters['order'] }}" />
					<input type="hidden" name="filter_order_dir" value="{{ $filters['order_dir'] }}" />
				</fieldset>
			</div>
			<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">

				<div id="applied-filters" class="mb-0" aria-label="{{ trans('software::software.applied filters') }}">
					<p class="sr-only visually-hidden">{{ trans('software::software.applied filters') }}:</p>
					<ul class="filters-list">
						<?php
						$allfilters = collect($filters);

						$keys = ['search', 'type', 'resource'];

						foreach ($keys as $key):
							if (!isset($filters[$key]) || !$filters[$key] || $filters[$key] == '*'):
								continue;
							endif;

							$f = $allfilters
								->reject(function($v, $k) use ($key)
								{
									$ks = ['limit', 'page', 'order', 'order_dir'];

									return (in_array($k, $ks));
								})
								->map(function($v, $k) use ($key)
								{
									if ($k == $key)
									{
										$v = '*';
										$v = (in_array($k, ['search']) ? '' : $v);
									}
									return $v;
								})
								->toArray();

							$val = $filters[$key];
							$val = ($val == '*' ? 'all' : $val);
							if ($key == 'status'):
								$val = trans('software::software.' . $val);

							endif;
							if ($key == 'type'):
								foreach ($types as $type):
									if ($val == $type->alias):
										$val = $type->title;
										break;
									endif;
								endforeach;
							endif;

							if ($key == 'resource'):
								foreach ($resources as $resource):
									if ($val == $resource->id):
										$val = $resource->name;
										break;
									endif;
								endforeach;
							endif;
							?>
							<li class="mb-3">
								<strong>{{ trans('software::software.' . $key) }}</strong>: {{ $val }}
								<a href="{{ route('site.software.index', $f) }}" class="filters-x" title="{{ trans('software::software.remove filter') }}">
									<span class="fa fa-times" aria-hidden="true"><span class="sr-only visually-hidden">{{ trans('software::software.remove filter') }}</span>
								</a>
							</li>
							<?php
						endforeach;
						?>
					</ul>
				</div>

			@if (count($rows))
				<div class="software">
					@foreach ($rows as $i => $row)
					<div class="application card">
						<div class="card-body" id="application{{ $row->id }}">
							<div class="card-title"><a href="{{ route('site.software.show', ['alias' => $row->alias]) }}">{{ $row->title }}</a></div>
							<p>{!! App\Halcyon\Utility\Str::highlight(e($row->description), $filters['search']) !!}</p>
							<details>
								<summary>{{ trans('software::software.available versions') }}</summary>
								<div>
									<table class="table table-bordered">
										<caption class="sr-only visually-hidden">{{ trans('software::software.available versions') }}</caption>
										<tbody>
											@foreach ($row->versionsByResource() as $resource => $versions)
												<tr>
													<th scope="row">{{ $resource }}:</th>
													<td>
														@foreach ($versions as $version)
															<span class="badge badge-secondary">{{ $version->title }}</span>
														@endforeach
													</td>
												</tr>
											@endforeach
										</tbody>
									</table>
								</div>
							</details>
						</div>
					
						<div class="card-footer">
							<div class="row application-options">
								<div class="col-md-6 text-muted">
									<span class="fa fa-folder" aria-hidden="true"></span> {{ $row->type->title }}
								</div>
								<div class="col-md-6 text-right">
								@if (auth()->user() && (auth()->user()->can('edit software') || auth()->user()->can('delete software')))
									@if (auth()->user()->can('edit software'))
										<a href="{{ route('site.software.edit', ['id' => $row->id]) }}" data-api="{{ route('api.software.read', ['id' => $row->id]) }}" class="btn btn-sm btn-edit tip" title="{{ trans('global.button.edit') }}">
											<span class="fa fa-pencil" aria-hidden="true"></span><span class="sr-only visually-hidden">{{ trans('global.button.edit') }} #{{ $row->id }}</span>
										</a>
									@endif
									@if (auth()->user()->can('delete software'))
										<a href="{{ route('site.software.delete', ['id' => $row->id]) }}" data-api="{{ route('api.software.delete', ['id' => $row->id]) }}" class="btn btn-sm btn-delete text-danger tip remove-application" title="{{ trans('global.button.delete') }}" data-confirm="{{ trans('global.confirm delete') }}">
											<span class="fa fa-trash" aria-hidden="true"></span><span class="sr-only visually-hidden">{{ trans('global.button.delete') }} #{{ $row->id }}</span>
										</a>
									@endif
								@endif
								</div>
							</div>
						</div>
					
					</div>
					@endforeach
				</div>

				{{ $rows->render() }}
			@else
				<div class="placeholder card text-center">
					<div class="placeholder-body card-body">
						<span class="fa fa-ban" aria-hidden="true"></span>
						<p>{{ trans('global.no results') }}</p>
					</div>
				</div>
			@endif

			</div>
		</form>

	</div>
</div>
@stop