@if (count($nodes))
<ul class="tree">
	@foreach ($nodes as $node)
		@include('knowledge::site.listitem', ['node' => $node, 'path' => $path, 'current' => $current, 'variables' => $variables])
	@endforeach
</ul>
@endif