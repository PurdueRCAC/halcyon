<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

$cls = '';
if (!empty($active)):
	$cls = ' active';
endif;
?>
<div class="media-files media-list{{ $cls }}" id="media-list">
	<form action="{{ route('admin.media.medialist', ['folder' => $folder]) }}" method="post" id="media-form-list" name="media-form-list">
		<div class="manager">
			<table>
				<thead>
					<tr>
						<th scope="col">{{ trans('media::media.list.name') }}</th>
						<th scope="col">{{ trans('media::media.list.size') }}</th>
						<th scope="col">{{ trans('media::media.list.type') }}</th>
						<th scope="col">{{ trans('media::media.list.modified') }}</th>
					<?php if (auth()->user()->can('manage media')): ?>
						<th scope="col"></th>
					<?php endif; ?>
					</tr>
				</thead>
				<tbody>
					<?php
					// Group files and folders
					$folders = array();
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
					endforeach;
					?>
				</tbody>
			</table>

			<input type="hidden" name="task" value="" />
			<input type="hidden" name="folder" value="{{ $folder }}" />
		</div>
	</form>
</div>
