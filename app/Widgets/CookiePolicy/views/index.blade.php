@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('widgets/cookiepolicy/css/cookiepolicy.css?v=' . filemtime(public_path() . '/widgets/cookiepolicy/css/cookiepolicy.css')) }}" />
@endpush

<div class="cookiepolicy" id="{{ $id }}">
	<div class="cookiepolicy-message">
		{!! $message !!}

		<a class="cookiepolicy-close" href="{{ $uri }}" data-duration="{{ $duration }}" title="{{ trans('widget.cookiepolicy::cookiepolicy.close') }}">
			<span>{{ trans('widget.cookiepolicy::cookiepolicy.close') }}</span>
		</a>
	</div>
</div>
<script src="{{ asset('widgets/cookiepolicy/js/cookiepolicy.js?v=' . filemtime(public_path() . '/widgets/cookiepolicy/js/cookiepolicy.js')) }}"></script>
