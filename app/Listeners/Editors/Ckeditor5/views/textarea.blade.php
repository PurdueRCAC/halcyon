

<textarea name="{{ $name }}" {!! $atts !!}>{{ $value }}</textarea>
<script type="application/json" id="{{ $id }}-ckeconfig">{!! $config !!}</script>

@push('scripts')
<script type="text/javascript" src="{{ timestamped_asset('listeners/editors/ckeditor5/js/ckeditor.js') }}"></script>
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function () {
    ClassicEditor
        .create(document.querySelector("#<?php echo $id; ?>"), JSON.parse(document.getElementById("<?php echo $id; ?>-ckeconfig").innerHTML))
        .catch(error => { console.error( error ); }
    );
});
</script>
@endpush