

<textarea name="{{ $name }}" {!! $atts !!}>{{ $value }}</textarea>

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('listeners/editors/simplemde/css/simplemde.min.css?v=' . filemtime(public_path() . '/listeners/editors/simplemde/css/simplemde.min.css')) }}" />
@endpush
@push('scripts')
<script src="{{ asset('listeners/editors/simplemde/js/simplemde.min.js?v=' . filemtime(public_path() . '/listeners/editors/simplemde/js/simplemde.min.js')) }}"></script>
<script>
jQuery(document).ready(function($){
var simplemde = new SimpleMDE({
    element: document.getElementById('<?php echo $id; ?>'),
    blockStyles: {
		bold: "**",
		italic: "_"
	},
    spellChecker: false,
	status: false,
    showIcons: ["bold", "italic", "heading", "strikethrough", "code", "quote", "unordered-list", "ordered-list", "link", "image", "horizontal-rule", "table"],
    hideIcons: ["side-by-side", "fullscreen", "guide", "undo", "redo"],
    autoDownloadFontAwesome: false
});
});
</script>
@endpush
