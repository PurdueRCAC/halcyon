@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('widgets/cookiepolicy/css/cookiepolicy.css') }}" />
@endpush

<div class="cookiepolicy" id="{{ $id }}">
	<div class="cookiepolicy-message">
		{!! $message !!}

		<a class="cookiepolicy-close" href="{{ $uri }}" data-duration="{{ $duration }}" title="{{ trans('widget.cookiepolicy::cookiepolicy.close') }}">
			<span>{{ trans('widget.cookiepolicy::cookiepolicy.close') }}</span>
		</a>
	</div>
</div>
<script src="{{ timestamped_asset('widgets/cookiepolicy/js/cookiepolicy.js') }}"></script>
