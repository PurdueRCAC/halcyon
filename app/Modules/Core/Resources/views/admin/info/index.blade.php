@extends('layouts.master')

@section('styles')
<style>
.writable {
	color: green;
}
.unwritable {
	color: red;
}
</style>
@stop

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
<form action="{{ route('admin.core.sysinfo') }}" method="post" name="adminForm" id="adminForm">
	<div class="card">
		<div class="tabs" id="config-document">
			<ul>
				<li><a href="#page-site">{{ trans('core::info.system information') }}</a></li>
				<li><a href="#page-phpsettings">{{ trans('core::info.php settings') }}</a></li>
				<li><a href="#page-config">{{ trans('core::info.configuration') }}</a></li>
				<li><a href="#page-directory">{{ trans('core::info.directory permissions') }}</a></li>
				<li><a href="#page-phpinfo">{{ trans('core::info.php info') }}</a></li>
			</ul>

			<div id="page-site" class="tab">
				<div class="row noshow">
					<div class="col-md-12">
						@include('core::admin.info.system', ['info' => $model->getInfo()])
					</div>
				</div>
			</div>

			<div id="page-phpsettings" class="tab">
				<div class="row noshow">
					<div class="col-md-12">
						@include('core::admin.info.phpsettings', ['info' => $model->getPhpSettings()])
					</div>
				</div>
			</div>

			<div id="page-config" class="tab">
				<div class="row noshow">
					<div class="col-md-12">
						@include('core::admin.info.config', ['info' => $model->getConfig()])
					</div>
				</div>
			</div>

			<div id="page-directory" class="tab">
				<div class="row noshow">
					<div class="col-md-12">
						@include('core::admin.info.directory', ['info' => $model->getDirectory()])
					</div>
				</div>
			</div>

			<div id="page-phpinfo" class="tab">
				<div class="row noshow">
					<div class="col-md-12">
						@include('core::admin.info.phpinfo', ['info' => $model->getPhpInfo()])
					</div>
				</div>
			</div>
		</div>
	</div>
	@csrf
</form>
@stop