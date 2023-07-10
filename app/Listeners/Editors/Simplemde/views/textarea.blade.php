

<textarea name="{{ $name }}" {!! $atts !!}>{{ $value }}</textarea>

@if ($assets)
@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('listeners/editors/simplemde/css/simplemde.min.css') }}" />
@endpush
@push('scripts')
<script src="{{ timestamped_asset('listeners/editors/simplemde/js/simplemde.min.js') }}"></script>
<script src="{{ timestamped_asset('listeners/editors/simplemde/js/simplemde.js') }}"></script>
@endpush
@endif