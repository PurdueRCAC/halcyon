<?php if ($params->get('show_title')): ?>
	<{{ $params->get('item_heading', 'h3') }}>
		{{ $widget->title }}
	</{{ $params->get('item_heading', 'h3') }}>
<?php endif; ?>

<div id="wdgt-{{ $widget->id }}-carousel" class="carousel slide" data-ride="carousel">
	<div class="carousel-inner">
		<?php
		$i = 0;
		foreach ($files as $file):
			$path = ltrim($file->getRelativePath(), '/');
			$ext  = strtolower($file->getExtension());
			$href = route('admin.media.download', ['path' => $path]);
			?>
			<div class="text-center carousel-item<?php if ($i == 0) { echo ' active'; } ?>">
				<img src="{{ $file->getUrl() }}" class="d-block mx-auto" height="{{ $params->get('height', 400) }}" alt="{{ trans('media::media.image title', ['name' => $file->getFilename(), 'size' => $file->getFormattedSize()]) }}" />
			</div>
			<?php
			$i++;
		endforeach;
		?>
	</div>
	<button class="btn carousel-control-prev" type="button" data-target="#wdgt-{{ $widget->id }}-carousel" data-slide="prev">
		<span class="carousel-control-prev-icon" aria-hidden="true"></span>
		<span class="sr-only">Previous</span>
	</button>
	<button class="btn carousel-control-next" type="button" data-target="#wdgt-{{ $widget->id }}-carousel" data-slide="next">
		<span class="carousel-control-next-icon" aria-hidden="true"></span>
		<span class="sr-only">Next</span>
	</button>
</div>
