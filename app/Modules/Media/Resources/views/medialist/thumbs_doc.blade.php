<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

$path = ltrim($file->getRelativePath(), '/');
$ext  = $file->getExtension();
$href = route('admin.media.download') . '?file=' . $file->getRelativePath();

$icon = asset('modules/media/filetypes/' . $ext . '.svg');
if (!$icon):
	$icon = asset('modules/media/filetypes/file.svg');
endif;

// Before display event
//$params = new App\Halcyon\Config\Registry;
//event('onContentBeforeDisplay', array('media.file', $file, $params));
?>
		<div class="media-item media-item-thumb">
			<div class="media-preview">
				<div class="media-preview-inner">
					<a href="{{ $file->getUrl() }}" class="media-thumb doc-item {{ $ext }}" title="{{ $file->getFilename() }}" >
						<span class="media-preview-shim"></span><!--
						--><img src="{{ $icon }}" alt="{{ trans('media::media.IMAGE_TITLE', ['name' => $file->getFilename(), 'size' => $file->getFormattedSize()]) }}" width="80" />
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
					<?php if (auth()->user()->can('delete media')): ?>
						<li>
							<span class="separator"></span>
						</li>
						<li>
							<a class="icon-trash media-opt-delete" href="{{ route('admin.media.delete', ['file' => $path]) }}" data-api="{{ route('api.media.delete', ['items[0][path]' => $path, 'items[0][type]' => 'file']) }}">{{ trans('global.button.delete') }}</a>
						</li>
					<?php endif; ?>
					</ul>
				</div>

				@include('media::medialist.info')
				@include('media::medialist.path')
			</div>
		</div>
<?php
//event('onContentAfterDisplay', array('media.file', $file, $params));
