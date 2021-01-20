@if (count($nodes))
	@foreach ($nodes as $node)
		@include('knowledge::site.article', ['node' => $node, 'path' => $path, 'variables' => $variables])
	@endforeach
@endif