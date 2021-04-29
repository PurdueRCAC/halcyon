<?php
$name = $file->getShortName();
$path = ltrim($file->getRelativePath(), '/');
?>
	<tr class="media-item media-item-list">
		<td width="<?php echo (!auth()->user()->can('delete media')) ? '70' : '60'; ?>%">
			<a class="folder-item" data-folder="/{{ $path }}" data-href="{{ route('admin.media.medialist', ['folder' => '/' . $path]) }}" href="{{ route('admin.media.index', ['folder' => '/' . $path]) }}">
				<span class="media-icon">
					<img src="{{ asset('modules/media/filetypes/folder.svg') }}" alt="{{ $file->getFilename() }}" />
				</span>
				<span class="media-name">
					{{ $name }}
				</span>
			</a>
		</td>
		<td>
			<!-- Nothing here -->
		</td>
		<td>
			<span class="media-type">{{ trans('media::media.folder') }}</span>
		</td>
		<td>
			<time class="media-modified" datetime="{{ $file->getLastModified()->format('Y-m-d\TH:i:s\Z') }}">{{ $file->getLastModified()->format('Y-m-d H:i:s') }}</time>
		</td>
		<td>
			<div class="media-preview-inner">
				<span class="media-options-btn"></span>
				<div class="media-options">
					<ul>
						<li>
							<a class="icon-info media-opt-info" href="#fileinfo-{{ $file->getId() }}">{{ trans('media::media.file info') }}</a>
						</li>
						@if (auth()->user()->can('edit media'))
							<li>
								<a class="icon-edit media-opt-rename" href="{{ route('admin.media.medialist', ['folder' => '/' . $path]) }}" data-path="{{ dirname($path) }}" data-name="{{ basename($path) }}" data-prompt="New name">{{ trans('media::media.rename') }}</a>
							</li>
						@endif
						@if (auth()->user()->can('delete media'))
							<li>
								<span class="separator"></span>
							</li>
							<li>
								<a class="icon-trash media-opt-delete" href="{{ route('admin.media.delete', ['file' => $path]) }}" data-api="{{ route('api.media.rename') }}" data-api="{{ route('api.media.delete', ['items[0][path]' => $path, 'items[0][type]' => 'dir']) }}">{{ trans('global.button.delete') }}</a>
							</li>
						@endif
					</ul>
				</div>
			</div>
		</td>
	</tr>