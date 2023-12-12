@php
$page->gatherMetadata();
@endphp
<!DOCTYPE html>
<html dir="ltr" lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="no-js">
	<head>
		<!-- Metadata -->
		<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="csrf-token" content="{{ csrf_token() }}">
		<meta name="base-url" content="{{ rtrim(asset('/'), '/') }}">
		<meta name="api-token" content="{{ (Auth::user() ? Auth::user()->api_token : '') }}">
		<meta name="theme-color" content="#000000">

		<title>{{ config('app.name') }} - {{ $page->title }}</title>

		@if ($page->metadesc)
			<meta name="description" content="{{ $page->metadesc }}" />
		@endif
		@if ($page->metakey)
			<meta name="keywords" content="{{ $page->metakey }}" />
		@endif

		@if ($page->metadata)
			@foreach ($page->metadata->all() as $k => $v)
				@if ($v)
					@if ($v == '__comment__')
						{!! $k !!}
					@else
						<meta name="{{ $k }}" content="{{ $v }}" />
					@endif
				@endif
			@endforeach
		@endif

		<!-- Styles -->
		@if (count($page->styles))
			@foreach ($page->styles as $v)
				@php
				if (substr($v, 0, 4) != 'http' && substr($v, 0, 3) != '://'):
					$pth = asset($v);
					if (file_exists(public_path($v))):
						$pth .= '?v=' . filemtime(public_path($v));
					endif;
					$v = $pth;
				endif;
				@endphp
				<link rel="stylesheet" type="text/css" href="{{ $v }}" />
			@endforeach
		@endif

		<!-- Scripts -->
		@if (count($page->scripts))
			@foreach ($page->scripts as $v)
				@php
				if (substr($v, 0, 4) != 'http' && substr($v, 0, 3) != '://'):
					$pth = asset($v);
					if (file_exists(public_path($v))):
						$pth .= '?v=' . filemtime(public_path($v));
					endif;
					$v = $pth;
				endif;
				@endphp
				<script src="{{ $v }}"></script>
			@endforeach
		@endif
	</head>
	<body>
		<article id="article-content{{ $page->id }}">
			@if ($page->params->get('show_title', 1))
				<h1>{{ $page->title }}</h1>
			@endif

			@if ($page->params->get('show_author') || $page->params->get('show_create_date') || $page->params->get('show_modify_date') || $page->params->get('show_hits'))
				<dl class="article-info">
					<dt class="article-info-term">{{ trans('pages::pages.article info') }}</dt>
				@if ($page->params->get('show_create_date'))
					<dd class="create">
						{{ trans('pages::pages.created on', ['date' => $page->created_at->toDateTimeString()]) }}
					</dd>
				@endif
				@if ($page->params->get('show_modify_date'))
					<dd class="updated">
						{{ trans('pages::pages.last updated', ['date' => $page->updated_at->toDateTimeString()]) }}
					</dd>
				@endif
				@if ($page->params->get('show_author') && $page->creator->id)
					<dd class="createdby">
						{{ trans('pages::pages.article author', ['author' => $page->creator->name]) }}
					</dd>
				@endif
				@if ($page->params->get('show_hits'))
					<dd class="hits">
						{{ trans('pages::pages.article hits', ['hits' => $page->hits]) }}
					</dd>
				@endif
				</dl>
			@endif

			{!! $page->body !!}
		</article>
	</body>
</html>
