<?php
if (!isset($folderDepth)):
	$folderDepth = 1;
endif;

foreach ($folderTree as $fold):
	$cls = '';

	$icon = asset('modules/media/filetypes/folder.svg');

	$open = 0;
	$p = array();
	if ($folderDepth == 1):
		$cls = ' open';
		$icon = asset('modules/media/filetypes/folder-open.svg');
	else:
		$fld = trim($folder, '/');
		$trail = explode('/', $fld);

		$p = explode('/', trim($fold['path'], '/'));

		foreach ($p as $i => $f):
			if (!isset($trail[$i])):
				break;
			endif;

			if ($p[$i] == $trail[$i]):
				$open++;
			endif;
		endforeach;

		if ($open && $open == count($p)):
			$cls = ' open';
		endif;
	endif;
	?>
	<details id="folder-{{ $fold['name'] . '-' . $folderDepth }}" class="{{ 'depth' . $folderDepth }}"<?php echo $cls; ?>>
		<summary>
			<a class="folder"
				data-folder="{{ '/' . $fold['path'] }}"
				data-href="{{ route('admin.media.medialist') . '?folder=/' . urlencode($fold['path']) }}"
				href="{{ route('admin.media.index') . '?folder=' . urlencode('/' . $fold['path']) }}">
				<span class="folder-icon">
					<img src="<?php echo $icon; ?>" alt="{{ $fold['name'] }}" />
				</span>
				{{ $fold['name'] }}
			</a>
		</summary>
		<div id="{{ $fold['name'] . '-' . $folderDepth }}">
			<?php
			if (isset($fold['children']) && count($fold['children'])):
				$temp = $folderTree;

				$folderTree = $fold['children'];
				$folders_id = 'id="folder-' . $fold['name'] . '"';
				$folderDepth++;
				?>
				@include('media::media.folders')
				<?php
				$folderTree = $temp;
				$folderDepth--;
			else:
				?>
				<span class="text-muted">{{ trans('media::media.no subdirectories') }}</span>
				<?php
			endif;
			?>
		</div>
	</details>
	<?php
endforeach;
