@if (!request()->ajax())
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="no-js" data-mode="{{ auth()->user()->facet('theme.admin.mode', app('themes')->getActiveTheme()->getParams('mode', 'light')) }}">
	<head>
		<!-- Metadata -->
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="csrf-token" content="{{ csrf_token() }}">
		<meta name="base-url" content="{{ rtrim(url('/'), '/') }}">
		<meta name="api-token" content="{{ (Auth::user() ? Auth::user()->api_token : '') }}">
		<meta name="theme-color" content="#000000">
		<meta name="color-scheme" content="light dark">

		<title>{{ config('app.name') }} - {{ trans('theme::admin.error') }}@hasSection('title'): @yield('title')@endif</title>

		<!-- Styles -->
		<?php
		$styles = array(
			//'themes/admin/vendor/font-awesome/font-awesome-css.min.css',
			'modules/core/vendor/bootstrap/bootstrap.min.css',
			'modules/core/vendor/jquery-ui/jquery-ui.min.css',
			'modules/core/vendor/jquery-datepicker/jquery.datepicker.css',
			'modules/core/vendor/jquery-timepicker/jquery.timepicker.css',
			'themes/admin/css/index.css',
		);
		foreach ($styles as $css):
			?>
			<link rel="stylesheet" type="text/css" media="all" href="{{ asset($css . '?v=' . filemtime(public_path() . '/' . $css)) }}" />
			<?php
		endforeach;
		?>
		<!--[if IE 9]>
			<link rel="stylesheet" type="text/css" media="screen" href="{{ asset('themes/admin/css/browser/ie9.css') }}" />
		<![endif]-->
		<!--[if lt IE 9]>
			<script src="{{ asset('js/html5.js') }}"></script>
			<link rel="stylesheet" type="text/css" media="screen" href="{{ asset('themes/admin/css/browser/ie8.css') }}" />
		<![endif]-->

		<!-- Scripts -->
		<?php
		$scripts = array(
			'modules/core/vendor/jquery/jquery.min.js',
			//'modules/core/vendor/bootstrap/bootstrap.bundle.min.js',
			'modules/core/vendor/jquery-ui/jquery-ui.min.js',
			'modules/core/vendor/jquery-timepicker/jquery.timepicker.js',
			'modules/core/js/core.js',
			'themes/admin/js/index.js',
		);
		foreach ($scripts as $script):
			?>
			<script type="text/javascript" src="{{ asset($script . '?v=' . filemtime(public_path() . '/' . $script)) }}"></script>
			<?php
		endforeach;
		?>
	</head>
	<body>
		<div id="container-main">
			<header id="header" role="banner">
				<h1>
					<a href="{{ url()->to('/') }}">
						<span class="logo-container">
							<span class="logo-shim"></span>
							@if ($file = app('themes')->getActiveTheme()->getParams('logo'))
								<img src="{{ asset($file) }}" alt="" width="47" />
							@else
								<?php echo file_get_contents(app_path('Themes/Admin/assets/images/halcyon.svg')); ?>
							@endif
						</span>
						<span class="app-name">{{ config('app.name') }}</span>
					</a>
				</h1>

				<ul class="user-options">
					<li data-title="{{ trans('theme::admin.open-close menu') }}"><!-- 
						--><a href="#nav" class="hamburger ico-menu"><!-- 
							--><span class="hamburger-box"><span class="hamburger-inner"></span></span>{{ trans('theme::admin.menu') }}<!-- 
						--></a><!-- 
					--></li>
				</ul>

				<nav id="nav" role="navigation" class="main-navigation">
					<div class="inner-wrap">
						<ul id="adminmenu">
							<li><a class="icon-dashboard" href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
						</ul>
					</div>
				</nav><!-- / .main-navigation -->

				<ul class="user-options">
					@if (Auth::check())
						<!-- <li data-title="{{ trans('theme::admin.toggle theme') }}">
							<a class="icon-sun" href="{{ request()->url() }}?theme=dark">{{ trans('theme::admin.toggle theme') }}</a>
						</li> -->
						<li data-title="{{ trans('theme::admin.logout') }}">
							<a class="icon-power logout" href="{{ route('logout') }}">{{ trans('theme::admin.logout') }}</a>
						</li>
					@else
						@if (app('request')->input('hidemainmenu'))
							<li class="disabled" data-title="{{ trans('theme::admin.login') }}">
								<span class="icon-power login">{{ trans('theme::admin.login') }}</span>
							</li>
						@else
							<li data-title="{{ trans('theme::admin.login') }}">
								<a class="icon-power login" href="{{ route('login') }}">{{ trans('theme::admin.login') }}</a>
							</li>
						@endif
					@endif
				</ul>
			</header><!-- / #header -->

			<div id="container-module">
				<main id="error-content">
@endif
						@yield('content')
@if (!request()->ajax())
						<!-- Content ends -->

						<noscript>
							{{ trans('global.warnjavascript') }}
						</noscript>
				</main>
				@include('partials.footer')
			</div><!-- / #wrap -->
		</div><!-- / #container-main -->
	</body>
</html>
@endif