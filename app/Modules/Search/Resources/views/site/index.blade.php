@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('search::search.search'),
		route('site.search.index')
	);

if ($filters['search'])
{
	app('pathway')
		->append(
			$filters['search'],
			route('site.search.index', ['search' => $filters['search']])
		);
}
@endphp

@section('title'){{ trans('ksearch::search.search') . ($filters['search'] ? ': ' . $filters['search'] : '') }}@stop

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/search/css/search.css') }}" />
@endpush

@section('content')
<h2>{{ trans('search::search.search') }}</h2>

<div class="row">
	<div class="contentInner col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="row">
			<div class="col-md-12">
				<form method="get" action="{{ route('site.search.index') }}">
					<div class="form-group">
						<label class="sr-only visually-hidden" for="site_search">{{ trans('search::search.search') }}</label>
						<span class="input-group">
							<input type="search" enterkeyhint="search" name="search" id="site_search" class="form-control" placeholder="{{ trans('search::search.search placeholder') }}" value="{{ $filters['search'] }}" />
							<span class="input-group-append">
								<input type="submit" class="input-group-text" value="{{ trans('global.submit') }}" />
							</span>
						</span>
					</div>
				</form>
			</div>
		</div>

		@if (count($rows))
			<ul class="article-list">
			@foreach ($rows as $row)
				<li>
					<article class="article">
						<h3 class="article-title">
							<a href="{{ $row->route }}">
								{!! App\Halcyon\Utility\Str::highlight($row->title, $filters['search']) !!}
							</a>
						</h3>
						<p class="article-metadata text-muted">
							{{ $row->route }}
						</p>
						<p class="article-body">
							{!! App\Halcyon\Utility\Str::highlight(App\Halcyon\Utility\Str::excerpt(strip_tags($row->text), $filters['search']), $filters['search']) !!}
						</p>
					</article>
				</li>
			@endforeach
			</ul>

			{{ $paginator->render() }}
		@else
			<div class="card mb-4">
				<div class="card-body text-muted text-center">{{ trans('global.no results') }}</div>
			</div>
		@endif
	</div>
</div>
@stop