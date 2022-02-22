

<textarea name="{{ $name }}" {!! $atts !!}>{{ $value }}</textarea>
<script id="{{ $id }}-ckeconfig" type="application/json">{!! $config !!}</script>

@if ($assets)
@push('scripts')
<script src="{{ asset('listeners/editors/ckeditor/ckeditor.js?v=' . filemtime(public_path() . '/listeners/editors/ckeditor/ckeditor.js')) }}"></script>
<script src="{{ asset('listeners/editors/ckeditor/adapters/jquery.js?v=' . filemtime(public_path() . '/listeners/editors/ckeditor/adapters/jquery.js')) }}"></script>
@endpush
@endif