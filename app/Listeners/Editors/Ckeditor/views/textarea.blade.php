

<textarea name="{{ $name }}" {!! $atts !!}>{{ $value }}</textarea>
<script id="{{ $id }}-ckeconfig" type="application/json">{!! $config !!}</script>

@if ($assets)
@push('scripts')
<script src="{{ asset('listeners/editors/ckeditor/ckeditor.js?v=' . filemtime(public_path() . '/listeners/editors/ckeditor/ckeditor.js')) }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.ckeditor-content').forEach(function(el){
        var cfg = document.getElementById(el.id + '-ckeconfig'),
            config = null;
        if (cfg) {
            config = JSON.parse(cfg.innerHTML);
        }
        CKEDITOR.replace(el.id, config);
        CKEDITOR.on('instanceReady', function(event) {
            event.editor.on('fileUploadRequest', function(evt) {
                var xhr = evt.data.fileLoader.xhr;

                xhr.setRequestHeader('Authorization', 'Bearer ' + document.querySelector('meta[name="api-token"]').getAttribute('content'));
                xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                xhr.setRequestHeader('X-File-Name', evt.data.fileLoader.fileName);
                xhr.setRequestHeader('X-File-Size', evt.data.fileLoader.total);

                var formData = new FormData();
                formData.append('upload', evt.data.fileLoader.file, evt.data.fileLoader.fileName);
                xhr.send( formData );
                //xhr.send(evt.data.fileLoader.file);

                // Prevented the default behavior.
                evt.stop();
            });
            event.editor.on('fileUploadResponse', function(evt) {
                // Prevent the default response handler.
                evt.stop();

                // Get XHR and response.
                var data = evt.data,
                    xhr = data.fileLoader.xhr,
                    response = JSON.parse(xhr.responseText);

                if (typeof response['message'] != 'undefined') {
                    // An error occurred during upload.
                    data.message = response['message'];
                    evt.cancel();
                } else {
                    for (var i = 0; i < response.data.length; i++) {
                        if (response.data[i].path == evt.data.fileLoader.fileName) {
                            data.url = response.data[i].url;
                            break;
                        }
                    }
                    if (!data.url) {
                        data.message = 'File not found.';
                        evt.cancel();
                    }
                }
            });
        });
    });
});
</script>
@endpush
@endif