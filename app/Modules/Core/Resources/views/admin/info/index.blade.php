@extends('layouts.master')

@push('styles')
<style>
.writable {
	color: green;
}
.unwritable {
	color: red;
}
</style>
@endpush

@php
app('pathway')
	->append(
		trans('core::info.system information'),
		route('admin.core.sysinfo')
	);
@endphp

@section('title')
{{ trans('core::info.system information') }}
@stop

@section('content')
<form action="{{ route('admin.core.sysinfo') }}" method="get" name="adminForm" id="adminForm">

	<div id="config-document">
		<nav class="container-fluid">
			<ul id="info-tabs" class="nav nav-tabs info-list" role="tablist">
				<li class="nav-item" role="presentation">
					<a href="#page-site" class="nav-link active" data-toggle="tab" data-bs-toggle="tab" role="tab" id="site-tab" aria-controls="page-site" aria-selected="true">{{ trans('core::info.system information') }}</a>
				</li>
				<li class="nav-item" role="presentation">
					<a href="#page-phpsettings" class="nav-link" data-toggle="tab" data-bs-toggle="tab" role="tab" id="phpsettings-tab" aria-controls="page-phpsettings" aria-selected="false">{{ trans('core::info.php settings') }}</a>
				</li>
				<li class="nav-item" role="presentation">
					<a href="#page-directory" class="nav-link" data-toggle="tab" data-bs-toggle="tab" role="tab" id="directory-tab" aria-controls="page-directory" aria-selected="false">{{ trans('core::info.directory permissions') }}</a>
				</li>
				<li class="nav-item" role="presentation">
					<a href="#page-phpinfo" class="nav-link" data-toggle="tab" data-bs-toggle="tab" role="tab" id="phpinfo-tab" aria-controls="page-phpinfo" aria-selected="false">{{ trans('core::info.php info') }}</a>
				</li>
			</ul>
		</nav>
		<div class="tab-content" id="config-tabs-content">
			<div id="page-site" class="tab-pane show active" role="tabpanel" aria-labelledby="site-tab">
				<div class="card">
					@include('core::admin.info.system', ['info' => $model->getInfo()])
				</div>
			</div>

			<div id="page-phpsettings" class="tab-pane" role="tabpanel" aria-labelledby="phpsettings-tab">
				<div class="card">
					@include('core::admin.info.phpsettings', ['info' => $model->getPhpSettings()])
				</div>
			</div>

			<div id="page-directory" class="tab-pane" role="tabpanel" aria-labelledby="directory-tab">
				<div class="card">
					@include('core::admin.info.directory', ['info' => $model->getDirectory()])
				</div>
			</div>

			<div id="page-phpinfo" class="tab-pane" role="tabpanel" aria-labelledby="phpinfo-tab">
				<div class="card">
					<div class="card-body">
						@include('core::admin.info.phpinfo', ['info' => $model->getPhpInfo()])
					</div>
				</div>
			</div>
		</div>
	</div>

</form>
@stop
