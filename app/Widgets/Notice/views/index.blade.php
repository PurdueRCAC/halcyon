@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('widgets/notice/css/notice.css') }}" />
@endpush

@if ($publish)
<div class="notice-banner" role="banner">
	<div id="{{ $id }}" class="notice alert alert-{{ $alertlevel . ($params->get('allowClose', 1) ? ' alert-dismissible' : '') }} mb-0{{ $params->get('htmlclass', '') ? ' ' . $params->get('htmlclass', '') : '' }}">
		{!! $message !!}
		@if ($params->get('allowClose', 1))
			<button type="button" class="close" data-dismiss="alert" aria-label="{{ trans('widget.notice::notice.close') }}">
				<span aria-hidden="true">&times;</span>
			</button>
		@endif
	</div>
</div>
@endif
