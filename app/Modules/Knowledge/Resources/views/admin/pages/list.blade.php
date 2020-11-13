@if (count($nodes))
<ul class="tree">
	@foreach ($nodes as $node)
		@include('knowledge::admin.pages.listitem', ['node' => $node])
	@endforeach
</ul>
@endif