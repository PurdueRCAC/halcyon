@extends('layouts.master')

@section('scripts')
<script type="text/javascript" src="{{ asset('modules/News/js/rss.js') }}"></script>
@stop

@section('content')
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	@include('news::site.menu', ['types' => $types, 'active' => 'feeds'])
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<h3>{{ trans('news::news.feeds') }}</h3>
	<ul class="rsscontainer">
		<li>
			<a class="rss" href="all"><i class="fa fa-rss-square" aria-hidden="true"></i> <strong>{{ trans('news::news.all news') }}</strong></a>
		</li>
		<?php
		$resourceNewsTypes = array();

		if (count($types))
		{
			foreach ($types as $n)
			{
				$info = '';
				if ($n->tagresources == 0)
				{
					$info = '<span class="rssCheckbox"><span class="alert alert-warning">Warning: will not filter based on the resources selected.</span></span>';
				}
				else
				{
					// Keep track of all the resources that do require resources so that we know what 
					// news to get when only grabbing a resources RSS.
					array_push($resourceNewsTypes, $n->id);
				}
				?>
				<li>
					<input class="rssCheckbox" value="{{ $n->name }}" type="checkbox" />
					<a target="_blank" class="rss" href="{{ route('site.news.feed', ['name' => $n->name]) }}"><i class="fa fa-rss-square" aria-hidden="true"></i> {{ $n->name }}</a>
					<?php echo $info; ?>
				</li>
				<?php
			}
		}

		// Force a comma at the end when imploding.
		array_push($resourceNewsTypes, '');
		?>
	</ul>

	<h3>{{ trans('news::news.resource feeds') }}</h3>
	<ul class="rsscontainer">
		<?php
		$resources = App\Modules\Resources\Entities\Asset::query()
			->where('listname', '!=', '')
			->orderBy('name', 'asc')
			->get();

		if (count($resources))
		{
			foreach ($resources as $r)
			{
				?>
				<li>
					<input class="rssCheckbox" value="{{ $r->name }}" type="checkbox" />
					<a target="_blank" id="{{ $r->name }}" class="rss" href="{{ route('site.news.feed', ['name' => implode(',', $resourceNewsTypes) . $r->name]) }}">
						<i class="fa fa-rss-square" aria-hidden="true"></i>
						{{ $r->name }}
					</a>
				</li>
				<?php
			}
		}
		?>
	</ul>

	<div class="rssCheckbox">
		<p>
			<a target="_blank" id="customRSS" class="rssLogo" href="#">{{ trans('news::news.custom feed') }}</a>
		</p>
	</div>

	<p>
		<button class="rssCustomize btn btn-default" data-txt="{{ trans('global.cancel') }}">{{ trans('news::news.customize feed') }}</button>
	</p>
</div>

@stop