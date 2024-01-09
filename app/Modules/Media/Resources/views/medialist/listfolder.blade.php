<?php
$name = $file->getFilename();
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
							<a class="media-opt-info" href="#fileinfo-{{ $file->getId() }}">
								<span class="fa fa-fw fa-info" aria-hidden="true"></span>
								{{ trans('media::media.file info') }}
							</a>
						</li>
						@if (auth()->user()->can('edit media'))
							<li>
								<a class="media-opt-rename" href="{{ route('admin.media.medialist', ['folder' => '/' . $path]) }}" data-api="{{ route('api.media.rename') }}" data-path="{{ dirname($path) }}" data-name="{{ basename($path) }}" data-prompt="{{ trans('media::media.new name') }}">
									<span class="fa fa-fw fa-pencil" aria-hidden="true"></span>
									{{ trans('media::media.rename') }}
								</a>
							</li>
							<li>
								<a class="media-opt-move" href="{{ route('admin.media.medialist', ['folder' => '/' . $path]) }}" data-api="{{ route('api.media.move') }}" data-path="{{ dirname($path) }}" data-name="{{ basename($path) }}" data-prompt="{{ trans('media::media.move prompt') }}">
									<span class="fa fa-fw fa-arrows" aria-hidden="true"></span>
									{{ trans('media::media.move') }}
								</a>
							</li>
						@endif
						@if (auth()->user()->can('delete media'))
							<li>
								<span class="separator"></span>
							</li>
							<li>
								<a class="media-opt-delete" href="{{ route('admin.media.delete', ['file' => $path]) }}" data-api="{{ route('api.media.delete', ['items[0][path]' => $path, 'items[0][type]' => 'dir']) }}">
									<span class="fa fa-fw fa-trash" aria-hidden="true"></span>
									{{ trans('global.button.delete') }}
								</a>
							</li>
						@endif
					</ul>
				</div>
			</div>
		</td>
	</tr>