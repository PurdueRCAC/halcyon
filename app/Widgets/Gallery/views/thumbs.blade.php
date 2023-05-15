<?php if ($params->get('show_title')): ?>
	<{{ $params->get('item_heading', 'h3') }}>
		{{ $widget->title }}
	</{{ $params->get('item_heading', 'h3') }}>
<?php endif; ?>

<div class="media-file media-thumbs" id="wdgt-{{ $widget->id }}-thumbs">
	<div>
	<div class="manager row">
		<?php
		foreach ($files as $file):
			$path = ltrim($file->getRelativePath(), '/');
			$ext  = strtolower($file->getExtension());
			$href = route('admin.media.download', ['path' => $path]);
			?>
			<div class="media-item media-item-thum col-md-3">
				<div class="media-preview">
					<div class="media-preview-inner">
						<a href="{{ $file->getUrl() }}" class="media-thumb doc-item img-preview {{ $ext }}" title="{{ $file->getFilename() }}">
							<span class="media-preview-shim"></span><!--
							--><img src="{{ $file->getUrl() }}" alt="{{ trans('media::media.image title', ['name' => $file->getFilename(), 'size' => $file->getFormattedSize()]) }}" width="160" />
						</a>
					</div>
				</div>
			</div>
			<?php
		endforeach;
		?>
	</div>
	</div>
</div>