@extends('layouts.master')

@section('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/media/css/media.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/media/vendor/dropzone-5.7.0/dist/min/dropzone.min.css') }}" />
<!-- <link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/media/vendor/jquery-file-upload/css/jquery.fileupload.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/media/vendor/jquery-file-upload/css/jquery.fileupload-ui.css') }}" /> -->
@stop

@section('scripts')
<!-- <script src="{{ asset('modules/media/js/jquery.fileuploader.js') }}"></script>
<script src="{{ asset('modules/media/vendor/jquery-file-upload/js/jquery.iframe-transport.js') }}"></script>
<script src="{{ asset('modules/media/vendor/jquery-file-upload/js/jquery.fileupload.js') }}"></script>
<script src="{{ asset('modules/media/vendor/jquery-file-upload/js/jquery.fileupload-process.js') }}"></script>
<script src="{{ asset('modules/media/vendor/jquery-file-upload/js/jquery.fileupload-validate.js') }}"></script>
<script src="{{ asset('modules/media/vendor/jquery-file-upload/js/jquery.fileupload-ui.js') }}"></script> -->
<script src="{{ asset('modules/media/vendor/dropzone-5.7.0/dist/min/dropzone.min.js') }}"></script>
<script src="{{ asset('modules/media/vendor/jquery-treeview/jquery.treeview.js') }}"></script>
<script src="{{ asset('modules/media/js/media.js') }}"></script>
@stop

@php
app('pathway')
	->append(
		trans('media::media.module name'),
		route('admin.media.index')
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('create media'))
		{!!
			Toolbar::append('Custom', '<a class="btn toolbar-btn media-upload" data-title="' . trans('media::media.upload') . '" href="#media-upload" data-api="' . route('api.media.upload') . '"><span class="icon-upload">' . trans('media::media.upload') . '</span></a>', 'upload');

			Toolbar::append('Custom', '<a class="btn toolbar-btn" data-title="' . trans('media::media.create folder') . '" href="' . route('admin.media.folder.create') . '" data-api="' . route('api.media.folder.create') . '" data-prompt="' . trans('media::media.folder name') . '"><span class="icon-folder-plus">' . trans('media::media.create folder') . '</span></a>', 'folder-new');
		!!}
	@endif
	@if (auth()->user()->can('admin media'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('media')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('content')

<div class="media-container modl">
	<div class="media-panels">
		<div class="panel panel-tree">
			<div id="media-tree_tree">
				@include('media::media.folders')
			</div>
		</div><!-- / .panel-tree -->
		<div class="panel panel-files">
			<form action="{{ route('admin.media.index') }}" name="adminForm" id="upload-form" method="post" enctype="multipart/form-data">
				<div class="media-header">
					<div class="media-breadcrumbs-block">
						<a href="{{ route('admin.media.medialist', ['folder' => '/']) }}" data-folder="/" class="media-breadcrumbs has-next-button folder-link" id="path_root">
							<img src="{{ asset('modules/media/filetypes/folder.svg') }}" alt="" />
						</a>
						<span id="media-breadcrumbs">
							<?php
							$fold = trim($folder, '/');
							$trail = explode('/', $fold);
							$trail = array_filter($trail);
							$fld = '';

							foreach ($trail as $crumb):
								// Skip the root directory
								/*if ($crumb == $folders[0]['name']):
									continue;
								endif;*/

								$fld .= '/' . $crumb;
								?>
								<span class="icon-chevron-right dir-separator">/</span>
								<a href="{{ route('admin.media.medialist', ['folder' => $fld]) }}" data-folder="{{ $fld }}" class="media-breadcrumbs folder has-next-button" id="path_{{ $crumb }}">{{ $crumb }}</a>
								<?php
							endforeach;
							?>
						</span>
					</div>
					<div class="media-header-buttons">
						<a class="media-files-view thumbs-view hasTip <?php if (!$layout || $layout == 'thumbs') { echo 'active'; } ?>" data-view="thumbs" href="<?php echo route('admin.media.index', ['layout' => 'thumbs']); ?>" data-tip="{{ trans('media::media.THUMBNAIL_VIEW') }}" title="{{ trans('media::media.THUMBNAIL_VIEW') }}">
							<!-- <i class="fa fa-th"></i> -->
							<span class="icon-grid"></span>
							{{ trans('media::media.THUMBNAIL_VIEW') }}
						</a>
						<a class="media-files-view hasTip listing-view <?php if ($layout == 'list') { echo 'active'; } ?>" data-view="list" href="<?php echo route('admin.media.index', ['layout' => 'list']); ?>" data-tip="{{ trans('media::media.DETAIL_VIEW') }}" title="{{ trans('media::media.DETAIL_VIEW') }}">
							<!-- <i class="fa fa-list"></i> -->
							<span class="icon-list"></span>
							{{ trans('media::media.DETAIL_VIEW') }}
						</a>

						<?php /*if (auth()->user()->can('create media')): ?>
							<a class="media-files-action media-folder-new"
								href="{{ route('admin.media.folder.create') }}"
								data-prompt="{{ trans('media::media.folder name') }}"
								data-tip="{{ trans('media::media.create folder') }}"
								data-api="{{ route('api.media.folder.create') }}"
								title="{{ trans('media::media.create folder') }}">
								<!-- <i class="fa fa-folder"></i> -->
								<span class="icon-folder-plus"></span>
								{{ trans('media::media.CREATE_FOLDER') }}
							</a>
						<?php endif; ?>

						<?php if (auth()->user()->can('create media')): ?>
							<?php
							//$this->js('jquery.fileuploader.js', 'system');
							?>
							<div id="ajax-uploader"
								data-action="{{ route('admin.media.upload') }}"
								data-list="{{ route('admin.media.medialist') }}"
								data-instructions="{{ trans('media::media.UPLOAD_INSTRUCTIONS') }}"
								data-instructions-btn="{{ trans('media::media.UPLOAD_INSTRUCTIONS_BTN') }}">
								<noscript>
									<div class="form-group">
										<label for="upload">{{ trans('media::media.UPLOAD_FILE') }}:</label>
										<input type="file" name="upload" id="upload" />
									</div>
								</noscript>
							</div>
							<!-- <div class="field-wrap file-list" id="ajax-uploader-list">
								<ul></ul>
							</div> -->
						<?php endif;*/ ?>
					</div>
				</div>
				<div class="media-view">
					<div class="media-items" id="media-items" data-tmpl="" data-confirm="{{ trans('media::media.confirm delete') }}" data-list="{{ route('admin.media.medialist') }}">
						<?php
						$children = App\Modules\Media\Helpers\MediaHelper::getChildren(storage_path() . '/app', '');
						?>
						@include('media::medialist.index')
					</div>
				</div>

				<?php /*if (auth()->user()->can('create media')): ?>
					<div class="dialoge dialog-upload" id="media-upload" title="{{ trans('media::media.upload') }}">
						<div id="ajax-uploader" class="fileinput-button qq-uploader"
							data-drop="#dropzone"
							data-action="{{ route('api.media.upload', ['api_token' => auth()->user()->api_token]) }}"
							data-list="{{ route('admin.media.medialist') }}"
							data-instructions="{{ trans('media::media.UPLOAD_INSTRUCTIONS') }}"
							data-instructions-btn="{{ trans('media::media.UPLOAD_INSTRUCTIONS_BTN') }}">

							<div class="form-group">
								<label for="files">{{ trans('media::media.UPLOAD_FILE') }}:</label>
								<input type="file" name="files[]" id="files" data-url="{{ route('api.media.upload') }}" multiple="multiple" />
							</div>
							<!-- <div id="dropzone" class="dropzone">Drop files here</div> -->
							<div id="file-uploader-list"></div>

						</div>
					</div>
					<script id="template-upload" type="text/x-tmpl">
					  {% for (var i=0, file; file=o.files[i]; i++) { %}
						  <tr class="template-upload fade{%=o.options.loadImageFileTypes.test(file.type)?' image':''%}">
							  <td>
								  <span class="preview"></span>
							  </td>
							  <td>
								  <p class="name">{%=file.name%}</p>
								  <strong class="error text-danger"></strong>
							  </td>
							  <td>
								  <p class="size">Processing...</p>
								  <div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
							  </td>
							  <td>
								  {% if (!o.options.autoUpload && o.options.edit && o.options.loadImageFileTypes.test(file.type)) { %}
									<button class="btn btn-success edit" data-index="{%=i%}" disabled>
										<i class="glyphicon glyphicon-edit"></i>
										<span>Edit</span>
									</button>
								  {% } %}
								  {% if (!i && !o.options.autoUpload) { %}
									  <button class="btn btn-primary start" disabled>
										  <i class="glyphicon glyphicon-upload"></i>
										  <span>Start</span>
									  </button>
								  {% } %}
								  {% if (!i) { %}
									  <button class="btn btn-warning cancel">
										  <i class="glyphicon glyphicon-ban-circle"></i>
										  <span>Cancel</span>
									  </button>
								  {% } %}
							  </td>
						  </tr>
					  {% } %}
					</script>
				<?php endif;*/ ?>

				<input type="hidden" name="task" value="" />
				<input type="hidden" name="folder" id="folder" value="{{ $folder }}" />
				<input type="hidden" name="layout" id="layout" value="{{ $layout }}" data-api="{{ route('api.media.layout') }}" />
				<?php if ($field = app('request')->input('e_name')): ?>
					<input type="hidden" name="e_name" id="e_name" value="{{ $field }}" />
				<?php endif; ?>
				<?php if ($field = app('request')->input('fieldid')): ?>
					<input type="hidden" name="fieldid" id="fieldid" value="{{ $field }}" />
				<?php endif; ?>
				@csrf
			</form>
<?php if (auth()->user()->can('create media')): ?>
		<div class="dialog dialog-upload" id="media-upload" title="{{ trans('media::media.upload') }}">
			<form action="{{ route('api.media.upload', ['api_token' => auth()->user()->api_token]) }}" id="uploader" class="dropzone">
				<div class="fallback">
					<input type="file" name="files" multiple />
				</div>
				@csrf
			</form>
		</div>
<?php endif; ?>
		</div><!-- / .panel-files -->
	</div><!-- / .media-panels -->
</div>
@stop
