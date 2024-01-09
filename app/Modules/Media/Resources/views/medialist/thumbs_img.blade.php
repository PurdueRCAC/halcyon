<?php
$path = ltrim($file->getRelativePath(), '/');
$ext  = strtolower($file->getExtension());
$href = route('admin.media.download', ['path' => $path]);
?>
<div class="media-item media-item-thumb">
	<div class="media-preview">
		<div class="media-preview-inner">
			<a href="{{ $file->getUrl() }}" class="media-thumb doc-item img-preview {{ $ext }}" title="{{ $file->getFilename() }}">
				<span class="media-preview-shim"></span><!--
				--><img src="{{ $file->getUrl() }}" alt="{{ trans('media::media.image title', ['name' => $file->getFilename(), 'size' => $file->getFormattedSize()]) }}" width="160" />
			</a>
			<span class="media-options-btn"></span>
		</div>
	</div>
	<div class="media-info">
		<div class="media-name">
			{{ $file->getShortName() }}
		</div>
		<div class="media-options">
			<ul>
				<li>
					<a class="media-opt-info" href="#fileinfo-{{ $file->getId() }}">
						<span class="fa fa-fw fa-info" aria-hidden="true"></span>
						{{ trans('media::media.file info') }}
					</a>
				</li>
				<li>
					<span class="separator"></span>
				</li>
				<li>
					<a download class="media-opt-download" href="{{ $href }}">
						<span class="fa fa-fw fa-download" aria-hidden="true"></span>
						{{ trans('media::media.download') }}
					</a>
				</li>
				<li>
					<a class="media-opt-path" href="#filepath-{{ $file->getId() }}">
						<span class="fa fa-fw fa-link" aria-hidden="true"></span>
						{{ trans('media::media.file link') }}
					</a>
				</li>
			@if (auth()->user()->can('edit media'))
				<li>
					<a class="media-opt-rename" href="{{ $href }}" data-api="{{ route('api.media.rename') }}" data-path="{{ dirname($path) }}" data-name="{{ basename($path) }}" data-prompt="{{ trans('media::media.new name') }}">
						<span class="fa fa-fw fa-pencil" aria-hidden="true"></span>
						{{ trans('media::media.rename') }}
					</a>
				</li>
				<li>
					<a class="media-opt-move" href="{{ $href }}" data-api="{{ route('api.media.move') }}" data-path="{{ dirname($path) }}" data-name="{{ basename($path) }}">
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
					<a class="media-opt-delete" href="{{ route('admin.media.delete', ['path' => $file->getRelativePath()]) }}" data-api="{{ route('api.media.delete', ['items[0][path]' => $path, 'items[0][type]' => 'file']) }}">
						<span class="fa fa-fw fa-trash" aria-hidden="true"></span>
						{{ trans('global.button.delete') }}
					</a>
				</li>
			@endif
			</ul>
		</div>

		@include('media::medialist.info')
		@include('media::medialist.path')
	</div>
</div>
