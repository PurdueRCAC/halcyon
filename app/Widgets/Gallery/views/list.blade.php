<?php if ($params->get('show_title')): ?>
	<{{ $params->get('item_heading', 'h3') }}>
		{{ $widget->title }}
	</{{ $params->get('item_heading', 'h3') }}>
<?php endif; ?>

<div class="media-files media-list" id="media-list">
	<div class="media-list">
		<table class="table">
			<caption class="sr-only visually-hidden">{{ trans('media::media.files') }}</caption>
			<thead>
				<tr>
					<th scope="col">{{ trans('media::media.list.name') }}</th>
					<th scope="col" class="text-nowrap text-right text-end">{{ trans('media::media.list.size') }}</th>
					<th scope="col">{{ trans('media::media.list.type') }}</th>
					<th scope="col">{{ trans('media::media.list.modified') }}</th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ($files as $file):
					$path = ltrim($file->getRelativePath(), '/');
					$ext  = strtolower($file->getExtension());
					$href = route('admin.media.download', ['path' => $path]);
					?>
					<tr class="media-item media-item-list">
						<td width="50%">
							<a class="doc-item" href="{{ $path }}" title="{{ $file->getFilename() }}">
								<span class="media-icon">
									<img src="{{ $file->getUrl() }}" width="50" alt="{{ trans('media::media.image title', ['name' => $file->getFilename(), 'size' => $file->getFormattedSize()]) }}" />
								</span>
								<span class="media-name">
									{{ $file->getFilename() }}
								</span>
							</a>
						</td>
						<td class="text-nowrap text-right text-end">
							<span class="media-size">{{ $file->getFormattedSize() }}</span>
						</td>
						<td>
							<span class="media-type">{{ strtoupper($ext) }}</span>
						</td>
						<td>
							<time class="media-modified" datetime="{{ $file->getLastModified()->format('Y-m-d\TH:i:s\Z') }}">{{ $file->getLastModified()->format('Y-m-d H:i:s') }}</time>
						</td>
					</tr>
					<?php
				endforeach;
				?>
			</tbody>
		</table>
	</div>
</div>
