
@if (count($rows))
	<ul id="{{ $id }}" class="sortable">
		@foreach ($rows as $i => $row)
			@include('menus::admin.items.listitem', ['rows' => $row->children, 'i' => $i])
		@endforeach
	</ul>
@endif
