

<textarea name="{{ $name }}" {!! $atts !!}>{{ $value }}</textarea>
<script id="{{ $id }}-ckeconfig" type="application/json">{!! $config !!}</script>

@push('scripts')
<script src="{{ asset('listeners/editors/ckeditor5/js/ckeditor.js?v=' . filemtime(public_path() . '/listeners/editors/ckeditor5/js/ckeditor.js')) }}"></script>
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function () {
ClassicEditor
    .create(document.querySelector("#<?php echo $id; ?>"), JSON.parse(document.getElementById("<?php echo $id; ?>-ckeconfig").innerHTML))
    .catch( error => { console.error( error ); }
);
});
</script>
@endpush