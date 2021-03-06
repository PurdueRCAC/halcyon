<?php
$cls = '';
if (!empty($active)):
	$cls = ' active';
endif;
?>
<div class="media-files media-list{{ $cls }}" id="media-list">
	<div action="{{ route('admin.media.medialist', ['folder' => $folder]) }}" method="post" id="media-form-list" name="media-form-list">
		<div class="manager">
			<table>
				<caption class="sr-only">{{ trans('media::media.files') }}</caption>
				<thead>
					<tr>
						<th scope="col">{{ trans('media::media.list.name') }}</th>
						<th scope="col" class="text-nowrap text-right">{{ trans('media::media.list.size') }}</th>
						<th scope="col">{{ trans('media::media.list.type') }}</th>
						<th scope="col">{{ trans('media::media.list.modified') }}</th>
					@if (auth()->user()->can('manage media'))
						<th scope="col"></th>
					@endif
					</tr>
				</thead>
				<tbody>
					<?php
					// Group files and folders
					/*$folders = array();
					$files = array();
					foreach ($children as $child):
						if ($child->isDir()):
							$folders[] = $child;
						else:
							$files[] = $child;
						endif;
					endforeach;

					// Display folders first
					foreach ($folders as $file):
						?>
						@include('media::medialist.listfolder')
						<?php
					endforeach;

					// Display files
					foreach ($files as $file):
						?>
						@include('media::medialist.listdoc')
						<?php
					endforeach;*/

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
				</tbody>
			</table>

			<!-- <input type="hidden" name="task" value="" />
			<input type="hidden" name="folder" value="{{ $folder }}" /> -->
		</div>
	</div>
</div>
