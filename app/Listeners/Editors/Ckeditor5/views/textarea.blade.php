

<textarea name="{{ $name }}" {!! $atts !!}>{{ $value }}</textarea>
<script type="application/json" id="{{ $id }}-ckeconfig">{!! $config !!}</script>

@push('scripts')
<script type="text/javascript" src="{{ timestamped_asset('listeners/editors/ckeditor5/js/ckeditor.js') }}"></script>
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function () {
   let config = {
        htmlSupport: {
            allow: [
                {
                    name: /.*/,
                    attributes: true,
                    classes: true,
                    styles: true
                }
            ]
        }
    };
    document.querySelectorAll('.ckeditor-content').forEach(function(el){
        ClassicEditor
            .create(el, config)
            .catch(error => { console.error( error ); }
        );
    });
});
</script>
@endpush