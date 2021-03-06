<?php
$path = ltrim($file->getRelativePath(), '/');
$ext  = strtolower($file->getExtension());
$href = route('admin.media.download', ['path' => $path]);

$icon = 'modules/media/filetypes/' . $ext . '.svg';
if (!file_exists(public_path($icon))):
	$icon = 'modules/media/filetypes/file.svg';
endif;
?>
	<tr class="media-item media-item-list">
		<td width="50%">
			<a class="doc-item" href="{{ $path }}" title="{{ $file->getFilename() }}">
				<span class="media-icon">
					<img src="{{ asset($icon) }}" alt="{{ trans('media::media.image title', ['name' => $file->getFilename(), 'size' => $file->getFormattedSize()]) }}" />
				</span>
				<span class="media-name">
					{{ $file->getFilename() }}
				</span>
			</a>
		</td>
		<td class="text-nowrap text-right">
			<span class="media-size">{{ $file->getFormattedSize() }}</span>
		</td>
		<td>
			<span class="media-type">{{ strtoupper($ext) }}</span>
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
						<li>
							<span class="separator"></span>
						</li>
						<li>
							<a download class="icon-download media-opt-download" href="{{ $href }}">{{ trans('media::media.download') }}</a>
						</li>
						<li>
							<a class="icon-link media-opt-path" href="#filepath-{{ $file->getId() }}">{{ trans('media::media.file link') }}</a>
						</li>
					@if (auth()->user()->can('edit media'))
						<li>
							<a class="icon-edit media-opt-rename" href="{{ $href }}" data-api="{{ route('api.media.rename') }}" data-path="{{ dirname($path) }}" data-name="{{ basename($path) }}" data-prompt="{{ trans('media::media.new name') }}">{{ trans('media::media.rename') }}</a>
						</li>
						<li>
							<a class="icon-move media-opt-move" href="{{ $href }}" data-api="{{ route('api.media.move') }}" data-path="{{ dirname($path) }}" data-name="{{ basename($path) }}">{{ trans('media::media.move') }}</a>
						</li>
					@endif
					@if (auth()->user()->can('delete media'))
						<li>
							<span class="separator"></span>
						</li>
						<li>
							<a class="icon-trash media-opt-delete" href="{{ route('admin.media.delete', ['path' => $path]) }}" data-api="{{ route('api.media.delete', ['items[0][path]' => $path, 'items[0][type]' => 'file']) }}">{{ trans('global.button.delete') }}</a>
						</li>
					@endif
					</ul>
				</div>
			</div>
		</td>
	</tr>
