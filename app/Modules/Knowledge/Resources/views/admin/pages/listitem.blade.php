@php
$p = (new App\Modules\Knowledge\Models\Page)->getTable();
$a = (new App\Modules\Knowledge\Models\SnippetAssociation)->getTable();
$children = App\Modules\Knowledge\Models\Page::query()
	->join($a, $a . '.page_id', $p . '.id')
	->select($p . '.title', $a . '.level', $a . '.lft', $a . '.rgt', $a . '.id', $a . '.path')
	->where($p . '.snippet', '=', 1)
	->where($a . '.parent_id', '=', $node->id)
	->orderBy('lft', 'asc')
	->get();

$cls = '';
if (count($children)):
	$cls .= 'parent';
endif;
@endphp
<li<?php if ($cls) { echo ' class="' . trim($cls) . '"'; } ?>>
	<span class="form-check">
		<input type="checkbox" name="snippets[]" id="snippet{{ $node->id }}" value="{{ $node->id }}" class="form-check-input checkbox-toggle" />
		<label for="snippet{{ $node->id }}" class="form-check-label">{{ $node->title }}<br /><span class="form-text text-muted">{{ $node->path }}</span></label>
	</span>

	@if (count($children))
		@include('knowledge::admin.pages.list', ['nodes' => $children])
	@endif
</li>