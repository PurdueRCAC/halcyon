@if ($editor->getEditorCssPartial() !== null)
	@if (Cache::store('array')->add('textareaCssLoaded', true, 100))
		@include($editor->getEditorCssPartial())
	@endif
@endif

<textarea name="{{ $name }}" {!! $atts !!}>{{ $value }}</textarea>

@if ($editor->getEditorJsPartial() !== null)
	@if (Cache::store('array')->add('textareaJsLoaded', true, 100))
		@include($editor->getEditorJsPartial())
	@endif
@endif