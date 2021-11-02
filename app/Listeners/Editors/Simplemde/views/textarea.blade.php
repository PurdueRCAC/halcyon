

<textarea name="{{ $name }}" {!! $atts !!}>{{ $value }}</textarea>

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('listeners/editors/simplemde/css/simplemde.min.css?v=' . filemtime(public_path() . '/listeners/editors/simplemde/css/simplemde.min.css')) }}" />
@endpush
@push('scripts')
<script src="{{ asset('listeners/editors/simplemde/js/simplemde.min.js?v=' . filemtime(public_path() . '/listeners/editors/simplemde/js/simplemde.min.js')) }}"></script>
<script src="{{ asset('listeners/editors/simplemde/js/simplemde.js?v=' . filemtime(public_path() . '/listeners/editors/simplemde/js/simplemde.js')) }}"></script>
@endpush
