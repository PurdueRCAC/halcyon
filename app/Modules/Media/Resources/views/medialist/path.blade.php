
<div class="dialog dialog-filepath" id="filepath-{{ $file->getId() }}" title="{{ trans('media::media.file path') }}">
	<div class="form-group">
		<input type="text" value="{{ $file->getUrl() }}" class="form-control" name="path" />
		<span class="form-text">{!! trans('media::media.use helper in content', ['helper' => '@file(\'' . $file->getPublicPath() . '\')']) !!}</span>
	</div>
</div>
