@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/media/vendor/dropzone-5.7.0/dist/min/dropzone.min.css') . '?v=' . filemtime(public_path() . '/modules/media/vendor/dropzone-5.7.0/dist/min/dropzone.min.css') }}" />
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/media/css/media.css') . '?v=' . filemtime(public_path() . '/modules/media/css/media.css') }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/media/vendor/dropzone-5.7.0/dist/min/dropzone.min.js') . '?v=' . filemtime(public_path() . '/modules/media/vendor/dropzone-5.7.0/dist/min/dropzone.min.js') }}"></script>
<script src="{{ asset('modules/core/vendor/jquery-cookie/jquery.cookie.js') . '?v=' . filemtime(public_path() . '/modules/core/vendor/jquery-cookie/jquery.cookie.js') }}"></script>
<script src="{{ asset('modules/media/vendor/jquery-treeview/jquery.treeview.js') . '?v=' . filemtime(public_path() . '/modules/media/vendor/jquery-treeview/jquery.treeview.js') }}"></script>
<script src="{{ asset('modules/media/js/media.js') . '?v=' . filemtime(public_path() . '/modules/media/js/media.js') }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('media::media.module name'),
		route('admin.media.index')
	);

	if (auth()->user()->can('create media')):
		Toolbar::append('Custom', '<a class="btn toolbar-btn media-upload" data-title="' . trans('media::media.upload') . '" href="#media-upload" data-api="' . route('api.media.upload') . '"><span class="icon-upload">' . trans('media::media.upload') . '</span></a>', 'upload');
		Toolbar::append('Custom', '<a class="btn toolbar-btn" data-title="' . trans('media::media.create folder') . '" href="' . route('admin.media.folder.create') . '" data-api="' . route('api.media.folder.create') . '" data-prompt="' . trans('media::media.folder name') . '"><span class="icon-folder-plus">' . trans('media::media.create folder') . '</span></a>', 'folder-new');
	endif;
	if (auth()->user()->can('admin media')):
		Toolbar::spacer();
		Toolbar::preferences('media');
	endif;
@endphp

@section('title')
{!! trans('media::media.module name') !!}
@stop

@section('toolbar')
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
						<a class="media-files-view thumbs-view hasTip <?php if (!$layout || $layout == 'thumbs') { echo 'active'; } ?>" data-view="thumbs" href="<?php echo route('admin.media.index', ['layout' => 'thumbs']); ?>" data-tip="{{ trans('media::media.thumbnail view') }}" title="{{ trans('media::media.thumbnail view') }}">
							<span class="icon-grid"></span>
							{{ trans('media::media.THUMBNAIL_VIEW') }}
						</a>
						<a class="media-files-view hasTip listing-view <?php if ($layout == 'list') { echo 'active'; } ?>" data-view="list" href="<?php echo route('admin.media.index', ['layout' => 'list']); ?>" data-tip="{{ trans('media::media.detail view') }}" title="{{ trans('media::media.detail view') }}">
							<span class="icon-list"></span>
							{{ trans('media::media.DETAIL_VIEW') }}
						</a>
					</div>
				</div>
				<div class="media-view">
					<div class="media-items" id="media-items" data-tmpl="" data-confirm="{{ trans('global.confirm delete') }}" data-list="{{ route('admin.media.medialist') }}">
						<?php
						$children = App\Modules\Media\Helpers\MediaHelper::getChildren(storage_path() . '/app' . $folder, '');
						?>
						@include('media::medialist.index')
					</div>
				</div>

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
