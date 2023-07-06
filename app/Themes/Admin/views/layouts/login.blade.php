<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="no-js">
	<head>
		<!-- Metadata -->
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="csrf-token" content="{{ csrf_token() }}" />
		<meta name="base-url" content="{{ rtrim(url('/'), '/') }}" />
		<meta name="api-token" content="{{ (auth()->user() ? auth()->user()->api_token : '') }}" />
		<meta name="theme-color" content="#000000" />
		<meta name="color-scheme" content="light dark" />

		<!-- Styles -->
		<?php
		$styles = array(
			'modules/core/vendor/bootstrap/bootstrap.min.css',
			'modules/core/vendor/jquery-ui/jquery-ui.min.css',
			'modules/core/vendor/jquery-datepicker/jquery.datepicker.css',
			'modules/core/vendor/jquery-timepicker-addon/jquery-ui-timepicker-addon.min.css',
			'themes/admin/css/index.css',
		);
		foreach ($styles as $css):
			?>
			<link rel="stylesheet" type="text/css" media="all" href="{{ asset($css . '?v=' . filemtime(public_path() . '/' . $css)) }}" />
			<?php
		endforeach;
		?>
		@yield('styles')
		@stack('styles')

		<!-- Scripts -->
		@include('partials.globals')
		<?php
		$scripts = array(
			'modules/core/vendor/jquery/jquery.min.js',
			'modules/core/vendor/bootstrap/bootstrap.bundle.min.js',
			'modules/core/vendor/jquery-ui/jquery-ui.min.js',
			'modules/core/js/core.js',
			'themes/admin/js/index.js',
		);
		foreach ($scripts as $script):
			?>
			<script type="text/javascript" src="{{ asset($script . '?v=' . filemtime(public_path() . '/' . $script)) }}"></script>
			<?php
		endforeach;
		?>
		@yield('scripts')
		@stack('scripts')
	</head>
	<body id="login-body" class="dark">

		<div id="container-main">
			<header id="header" role="banner">
				<h1>
					<a href="{{ url()->to('/') }}">
						<span class="logo-container">
							<span class="logo-shim"></span>
							<?php echo file_get_contents(app_path() . '/Themes/Admin/assets/images/halcyon.svg'); ?>
						</span>
						{{ config('app.name') }}
					</a>
				</h1>
			</header><!-- / #header -->

			<div id="wrap">
				<section id="component-content">
					<div id="toolbar-box">
						<h2>{{ trans('theme::admin.admin login') }}</h2>
					</div>

					<section id="main">
						<!-- Notifications begins -->
						@include('message')
						<!-- Notifications ends -->

						<!-- Content begins -->
						@yield('content')
						<!-- Content ends -->

						<noscript>
							{{ trans('global.warn javascript required') }}
						</noscript>
					</section><!-- / #main -->
				</section><!-- / #component-content -->
			</div><!-- / #wrap -->

		</div>
	</body>
</html>