@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('widgets/notice/css/notice.css') }}" />
@endpush

@if ($publish)
	<div id="{{ $id }}" class="notice alert alert-{{ $alertlevel . ($params->get('allowClose', 1) ? ' alert-dismissible' : '') }} mb-0">
		<p class="mb-0">
			{{ $message }}
			@if ($params->get('allowClose', 1))
				<button type="button" class="close" data-dismiss="alert" aria-label="{{ trans('widget.notice::notice.close') }}">
					<span aria-hidden="true">&times;</span>
				</button>
			@endif
		</p>
	</div>
@endif
