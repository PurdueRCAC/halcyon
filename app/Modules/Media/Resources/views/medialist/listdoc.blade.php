<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

$path = ltrim($file->getRelativePath(), '/');
$ext  = $file->getExtension();
$href = route('admin.media.download') . '?file=' . $path;

$icon = asset('modules/media/filetypes/' . $ext . '.svg');
if (!$icon):
	$icon = asset('modules/media/filetypes/file.svg');
endif;

//event('onContentBeforeDisplay', array('media.file', $file, $params));
?>
	<tr class="media-item media-item-list">
		<td width="50%">
			<a class="doc-item" href="{{ $path }}" title="{{ $file->getFilename() }}">
				<span class="media-icon">
					<img src="{{ $icon }}" alt="{{ trans('media::media.image title', ['name' => $file->getFilename(), 'size' => $file->getFormattedSize()]) }}" />
				</span>
				<span class="media-name">
					{{ $file->getShortName() }}
				</span>
			</a>
		</td>
		<td>
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
			</div>
		</td>
	</tr>
<?php
//event('onContentAfterDisplay', array('media.file', $file, $params));
