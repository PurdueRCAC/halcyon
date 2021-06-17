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

		<title>{{ config('app.name') }} - {{ trans('theme::admin.admin') }}@hasSection('title'): @yield('title')@endif</title>

		<!-- Styles -->
		<?php
		$styles = array(
			//'themes/admin/vendor/font-awesome/font-awesome-css.min.css',
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
		<!--[if IE 9]>
			<link rel="stylesheet" type="text/css" media="screen" href="{{ asset('themes/admin/css/browser/ie9.css') }}" />
		<![endif]-->
		<!--[if lt IE 9]>
			<script src="{{ asset('js/html5.js') }}"></script>
			<link rel="stylesheet" type="text/css" media="screen" href="{{ asset('themes/admin/css/browser/ie8.css') }}" />
		<![endif]-->
		@yield('styles')
		@stack('styles')

		<!-- Scripts -->
		<?php
		$scripts = array(
			'modules/core/vendor/jquery/jquery.min.js',
			//'modules/core/vendor/bootstrap/bootstrap.bundle.min.js',
			'modules/core/vendor/jquery-ui/jquery-ui.min.js',
			'modules/core/vendor/jquery-timepicker-addon/jquery-ui-timepicker-addon.min.js',
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
	<body{!! (auth()->user()->facet('theme.admin.menu') == 'open' ? ' class="menu-open"' : '') !!}>
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
						--><a href="#nav" class="hamburger ico-menu" data-api="{{ route('api.users.update', ['id' => auth()->user()->id]) }}"><!-- 
							--><span class="hamburger-box"><span class="hamburger-inner"></span></span>{{ trans('theme::admin.menu') }}<!-- 
						--></a><!-- 
					--></li>
				</ul>

				<nav id="nav" class="main-navigation" aria-label="{{ trans('theme::admin.main') }}">
					<div class="inner-wrap">
						@widget('menu')
					</div>
				</nav><!-- / .main-navigation -->

				<ul class="user-options">
					@if (Auth::check())
						<li data-title="{{ trans('theme::admin.toggle theme') }}">
							<a id="mode"
								data-api="{{ route('api.users.update', ['id' => auth()->user()->id]) }}"
								data-mode="{{ auth()->user()->facet('theme.admin.mode', 'light') == 'light' ? 'dark' : 'light' }}"
								href="{{ request()->url() }}?theme.admin.mode={{ auth()->user()->facet('theme.admin.mode', 'light') == 'light' ? 'dark' : 'light' }}">{{ trans('theme::admin.toggle theme') }}</a>
						</li>
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
				<main id="module-content">
					<div id="toolbar-box" class="toolbar-box">
						<div class="pagetitle">
							<h2 class="sr-only">@yield('title')</h2>

							@widget('breadcrumbs')

							@yield('toolbar')
						</div>
					</div><!-- / #toolbar-box -->

					<!-- Notifications begins -->
					@include('partials.notifications')
					<!-- Notifications ends -->

					<nav id="sub-nav" class="sub-navigation" aria-label="{{ trans('theme::app.module sections') }}">
						@widget('submenu')
					</nav><!-- / .sub-navigation -->

					<section id="main">
						<!-- Content begins -->
@endif
@yield('content')
@if (!request()->ajax())
						<!-- Content ends -->

						<noscript>
							{{ trans('global.warn javascript required') }}
						</noscript>
					</section><!-- / #main -->
				</main><!-- / #module-content -->

				@include('partials.footer')
			</div><!-- / #container-module -->
		</div><!-- / #container-main -->
	</body>
</html>
@endif