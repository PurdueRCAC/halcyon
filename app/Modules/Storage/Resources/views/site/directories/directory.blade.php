
	<details{!! $open ? ' open="true"' : '' !!} id="directory-{{ $dir['id'] }}">
		<summary class="row" data-id="{{ $dir['id'] }}">
			<div class="col-md-8">
				<a href="#{{ $dir['id'] }}_dialog" class="dir-modal" data-toggle="modal">
					<span class="tree-icon fa fa-folder" aria-hidden="true"></span><!--
					--><span class="tree-title">{{ $dir['title'] }}</span>
				</a>
			</div>
			<div class="col-md-2 text-right">{{ isset($dir['data']['bytes']) && $dir['data']['bytes'] ? $dir['quota'] : '-' }}</div>
			<div class="col-md-2 text-right">{!! isset($dir['data']['futurequota']) && $dir['data']['futurequota'] ? $dir['data']['futurequota'] : '-' !!}</div>
		</summary>
		@if (isset($dir['children']))
			@foreach ($dir['children'] as $child)
				@include('storage::site.directories.directory', ['dir' => $child, 'open' => false])
			@endforeach
		@endif
		<a href="#new_dir_dialog"
			class="btn btn-sm btn-newdir"
			data-toggle="modal"
			data-parent="{{ $dir['id'] }}"
			data-path="{{ $dir['data']['path'] }}"
			data-parentunixgroup="{{ $dir['data']['parentunixgroup'] }}"
			data-parentquota="{{ $dir['data']['parentquota'] }}">
			<span class="text-success fa fa-plus-circle" aria-hidden="true"></span> Add New Directory
		</a>
	</details>
