<?php
$cls = '';
if (!empty($active)):
	$cls = ' active';
endif;
?>
<div class="media-files media-thumbs<?php echo $cls; ?>" id="media-thumbs">
	<div action="{{ route('admin.media.medialist', ['folder' => $folder]) }}" method="post" id="media-form-thumbs" name="media-form-thumbs">
		<div class="manager">
			<?php
			/*$folders = array();
			$files = array();

			// Group files and folders
			foreach ($children as $child):
				if ($child->isDir()):
					$folders[] = $child;
				else:
					$files[] = $child;
				endif;
			endforeach;

			// Display files
			foreach ($files as $file):
				if ($file->isImage()):
					?>
					@include('media::medialist.thumbs_img')
					<?php
				else:
					?>
					@include('media::medialist.thumbs_doc')
					<?php
				endif;
			endforeach;*/

			// Display folders first
			foreach ($children as $file):
				if ($file->isDir()):
					?>
					@include('media::medialist.thumbs_folder')
					<?php
				elseif ($file->isImage()):
					?>
					@include('media::medialist.thumbs_img')
					<?php
				else:
					?>
					@include('media::medialist.thumbs_doc')
					<?php
				endif;
			endforeach;
			?>

			<!-- <input type="hidden" name="task" value="" />
			<input type="hidden" name="folder" value="{{ $folder }}" /> -->
		</div>
	</div>
</div>