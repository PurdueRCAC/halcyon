
<div class="dialog dialog-filepath" id="filepath-{{ $file->getId() }}" title="{{ trans('media::media.file path') }}">
	<div class="form-group">
		<input type="text" value="{{ $file->getUrl() }}" class="form-control" name="path" />
		<span class="form-text">Or use <code><?php echo '@file(\'' . $file->getPublicPath() . '\')'; ?></code> in content</span>
	</div>
</div>
