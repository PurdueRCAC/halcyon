<div id="filepath-{{ $file->getId() }}" class="modal fade dialog-filepath" tabindex="-1" aria-labelledby="filepath-{{ $file->getId() }}-title" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title mt-0" id="filepath-{{ $file->getId() }}-title">{{ trans('media::media.file path') }}</h3>
				<button type="button" class="btn-close close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
					<span class="visually-hidden" aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body text-left text-start">
				<div class="form-group">
					<input type="text" value="{{ $file->getUrl() }}" class="form-control" name="path" />
					<span class="form-text">{!! trans('media::media.use helper in content', ['helper' => '@file(\'' . $file->getPublicPath() . '\')']) !!}</span>
				</div>
			</div>
		</div>
	</div>
</div><!-- / .modal -->
