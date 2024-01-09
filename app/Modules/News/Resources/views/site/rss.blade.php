@extends('layouts.master')

@section('meta')
		<meta name="description" content="{{ trans('news::news.rss feeds') }}" />
@stop

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

@section('title'){{ trans('news::news.feeds') }}@stop

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/news/css/news.css') }}" />
@endpush

@push('scripts')
<script type="text/javascript" src="{{ timestamped_asset('modules/news/js/rss.js') }}"></script>
@endpush

@php
app('pathway')
	->append(
		config('module.news.module name', trans('news::news.news')),
		route('site.news.index')
	)
	->append(
		trans('news::news.rss feeds'),
		route('site.news.rss')
	);
@endphp

@section('content')
<div class="row">
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	@include('news::site.menu', ['types' => $types, 'active' => 'feeds'])
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<h2>{{ trans('news::news.rss feeds') }}</h2>
	<h3>{{ trans('news::news.feeds') }}</h3>
	<ul class="rsscontainer">
		<li class="form-check">
			<a class="rss" href="{{ route('site.news.feed', ['name' => 'all']) }}">
				<span class="fa fa-rss-square" aria-hidden="true"></span> <strong>{{ trans('news::news.all news') }}</strong>
			</a>
		</li>
		<?php
		$resourceNewsTypes = array();
		$types = App\Modules\News\Models\Type::tree();

		if (count($types)):
			foreach ($types as $n):
				$info = '';
				if ($n->tagresources == 0):
					$info = '<span class="rssCheckbox d-none"><span class="text-warning">' . trans('news::news.error.selection will not filter') . '</span></span>';
				else:
					// Keep track of all the resources that do require resources so that we know what 
					// news to get when only grabbing a resources RSS.
					array_push($resourceNewsTypes, $n->id);
				endif;
				?>
				<li class="form-check">
					@if ($n->level > 0)
						<span class="text-muted">{!! str_repeat('|&mdash;', $n->level) !!}</span>
					@endif
					<input class="form-check-input rssCheckbox d-none" value="{{ $n->name }}" id="checkbox{{ str_replace(' ', '', $n->name) }}" type="checkbox" />
					<label class="form-check-label" for="checkbox{{ str_replace(' ', '', $n->name) }}">
						<a target="_blank" class="rss" href="{{ route('site.news.feed', ['name' => $n->name]) }}">
							<span class="fa fa-rss-square" aria-hidden="true"></span> {{ $n->name }}
						</a>
					</label>
					<?php echo $info; ?>
				</li>
				<?php
			endforeach;
		endif;

		// Force a comma at the end when imploding.
		array_push($resourceNewsTypes, '');
		?>
	</ul>

	@if (\Nwidart\Modules\Facades\Module::isEnabled('resources'))
		<h3>{{ trans('news::news.resource feeds') }}</h3>
		<ul class="rsscontainer">
			<?php
			$resources = App\Modules\Resources\Models\Asset::query()
				->where('listname', '!=', '')
				->orderBy('name', 'asc')
				->get();

			if (count($resources)):
				foreach ($resources as $r):
					?>
					<li class="form-check">
						<input class="form-check-input rssCheckbox d-none" value="{{ $r->name }}" id="checkbox_{{ str_replace(' ', '', $r->name) }}" type="checkbox" />
						<label class="form-check-label" for="checkbox_{{ str_replace(' ', '', $r->name) }}">
							<a target="_blank" id="{{ $r->name }}" class="rss" href="{{ route('site.news.feed', ['name' => implode(',', $resourceNewsTypes) . $r->name]) }}">
								<span class="fa fa-rss-square" aria-hidden="true"></span> {{ $r->name }}
							</a>
						</label>
					</li>
					<?php
				endforeach;
			endif;
			?>
		</ul>
	@endif

	<div class="rssCheckbox d-none">
		<p>
			<a target="_blank" id="customRSS" class="rssLogo" href="{{ route('site.news.rss') }}">{{ trans('news::news.custom feed') }}</a>
		</p>
	</div>

	<p>
		<button class="rssCustomize btn btn-secondary" data-txt="{{ trans('global.cancel') }}">{{ trans('news::news.customize feed') }}</button>
	</p>
</div>
</div>
@stop