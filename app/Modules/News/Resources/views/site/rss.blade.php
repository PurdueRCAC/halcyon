@extends('layouts.master')

@section('title'){{ trans('news::news.feeds') }}@stop

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/news/css/news.css?v=' . filemtime(public_path() . '/modules/news/css/news.css')) }}" />
@endpush

@push('scripts')
<script type="text/javascript" src="{{ asset('modules/news/js/rss.js?v=' . filemtime(public_path() . '/modules/news/js/rss.js')) }}"></script>
@endpush

@section('content')
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	@include('news::site.menu', ['types' => $types, 'active' => 'feeds'])
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<h3>{{ trans('news::news.feeds') }}</h3>
	<ul class="rsscontainer">
		<li class="form-check">
			<a class="rss" href="all"><i class="fa fa-rss-square" aria-hidden="true"></i> <strong>{{ trans('news::news.all news') }}</strong></a>
		</li>
		<?php
		$resourceNewsTypes = array();

		if (count($types)):
			foreach ($types as $n):
				$info = '';
				if ($n->tagresources == 0):
					$info = '<span class="rssCheckbox"><span class="alert alert-warning">Warning: will not filter based on the resources selected.</span></span>';
				else:
					// Keep track of all the resources that do require resources so that we know what 
					// news to get when only grabbing a resources RSS.
					array_push($resourceNewsTypes, $n->id);
				endif;
				?>
				<li class="form-check">
					<input class="form-check-input rssCheckbox" value="{{ $n->name }}" id="checkbox{{ str_replace(' ', '', $n->name) }}" type="checkbox" />
					<label class="form-check-label" for="checkbox{{ str_replace(' ', '', $n->name) }}">
						<a target="_blank" class="rss" href="{{ route('site.news.feed', ['name' => $n->name]) }}">
							<i class="fa fa-rss-square" aria-hidden="true"></i> {{ $n->name }}
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
					<input class="form-check-input rssCheckbox" value="{{ $r->name }}" id="checkbox_{{ str_replace(' ', '', $r->name) }}" type="checkbox" />
					<label class="form-check-label" for="checkbox_{{ str_replace(' ', '', $r->name) }}">
						<a target="_blank" id="{{ $r->name }}" class="rss" href="{{ route('site.news.feed', ['name' => implode(',', $resourceNewsTypes) . $r->name]) }}">
							<i class="fa fa-rss-square" aria-hidden="true"></i> {{ $r->name }}
						</a>
					</label>
				</li>
				<?php
			endforeach;
		endif;
		?>
	</ul>

	<div class="rssCheckbox">
		<p>
			<a target="_blank" id="customRSS" class="rssLogo" href="{{ route('site.news.rss') }}">{{ trans('news::news.custom feed') }}</a>
		</p>
	</div>

	<p>
		<button class="rssCustomize btn btn-secondary" data-txt="{{ trans('global.cancel') }}">{{ trans('news::news.customize feed') }}</button>
	</p>
</div>
@stop