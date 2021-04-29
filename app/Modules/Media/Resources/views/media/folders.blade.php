<?php
if (!isset($folderDepth)):
	$folderDepth = 1;
endif;
?>
<ul <?php echo $folders_id; ?> class="{{ 'depth' . $folderDepth }}">
	<?php foreach ($folderTree as $fold) : ?>
		<?php
		$cls = '';

		$icon = asset('modules/media/filetypes/folder.svg');

		$open = 0;
		$p = array();
		if ($folderDepth == 1):
			$cls = ' class="open"';
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
				$cls = ' class="open"';
			endif;
		endif;
		?>
		<li id="{{ $fold['name'] }}"<?php echo $cls; ?>>
			<a class="folder" data-folder="{{ '/' . $fold['path'] }}" data-href="{{ route('admin.media.medialist') . '?folder=/' . urlencode($fold['path']) }}" href="{{ route('admin.media.index') . '?folder=' . urlencode('/' . $fold['path']) }}">
				<span class="folder-icon">
					<img src="<?php echo $icon; ?>" alt="{{ $fold['name'] }}" />
				</span>
				{{ $fold['name'] }}
			</a>
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
			endif;
			?>
		</li>
	<?php endforeach; ?>
</ul>
