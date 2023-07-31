@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('widgets/cookiepolicy/css/cookiepolicy.css') }}" />
@endpush

<div class="cookiepolicy fixed-bottom shadow-lg" id="{{ $id }}">
	<div class="cookiepolicy-message text-center alert alert-warning m-0 p-4">
		{!! $message !!}

		<a class="btn btn-warning cookiepolicy-close" href="{{ $uri }}" data-target="{{ $id }}" data-duration="{{ $duration }}" title="{{ trans('widget.cookiepolicy::cookiepolicy.close') }}">
			<span>{{ trans('widget.cookiepolicy::cookiepolicy.close') }}</span>
		</a>
	</div>
</div>
<script src="{{ timestamped_asset('widgets/cookiepolicy/js/cookiepolicy.js') }}"></script>
