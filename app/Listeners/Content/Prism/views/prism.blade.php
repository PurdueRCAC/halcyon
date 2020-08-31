@push('scripts')
<script src="{{ asset('listeners/content/prism/vendor/prism/prism.js?v=' . filemtime(public_path() . '/listeners/content/prism/vendor/prism/prism.js')) }}"></script>
@endpush

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('listeners/content/prism/vendor/prism/prism.css?v=' . filemtime(public_path() . '/listeners/content/prism/vendor/prism/prism.css')) }}" />
@endpush
