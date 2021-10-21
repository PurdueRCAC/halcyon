<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="no-js">
	<head>
		<!-- Metadata -->
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="csrf-token" content="{{ csrf_token() }}">

		<!-- Styles -->
		<link rel="stylesheet" type="text/css" media="all" href="{{ asset('themes/admin/css/login.css?v=' . filemtime(public_path() . '/themes/admin/css/login.css')) }}" />
		<!--[if lt IE 9]>
			<script src="{{ asset('themes/admin/js/html5.js') }}"></script>
		<![endif]-->
		<!--[if lte IE 9]>
			<link rel="stylesheet" type="text/css" media="screen" href="{{ asset('themes/admin/css/browser/ie.css') }}" />
		<![endif]-->
		@yield('styles')

		<!-- Scripts -->
		<script type="text/javascript" src="{{ asset('themes/admin/js/jquery.min.js?v=' . filemtime(public_path() . '/themes/admin/js/jquery.min.js')) }}"></script>
		<script type="text/javascript" src="{{ asset('themes/admin/js/login.js?v=' . filemtime(public_path() . '/themes/admin/js/login.js')) }}"></script>
		@yield('scripts')
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