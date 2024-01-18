@if (!request()->ajax())
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="no-js" data-mode="{{ auth()->user() ? auth()->user()->facet('theme.admin.mode', app('themes')->getActiveTheme()->getParams('mode', 'light')) : 'light' }}">
	<head>
		<!-- Metadata -->
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="csrf-token" content="{{ csrf_token() }}" />
		<meta name="base-url" content="{{ rtrim(url('/'), '/') }}" />
		<meta name="api-token" content="{{ (Auth::user() ? Auth::user()->api_token : '') }}" />
		<meta name="theme-color" content="#000000" />
		<meta name="color-scheme" content="light dark" />

		<title>{{ config('app.name') }} - {{ trans('theme::admin.error') }}@hasSection('title'): @yield('title')@endif</title>

		<!-- Styles -->
		<?php
		$styles = array(
			//'themes/admin/vendor/font-awesome/font-awesome-css.min.css',
			'modules/core/vendor/bootstrap/bootstrap.min.css',
			'modules/core/vendor/jquery-ui/jquery-ui.min.css',
			'themes/admin/css/index.css',
		);
		foreach ($styles as $css):
			?>
			<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset($css) }}" />
			<?php
		endforeach;
		?>

		<!-- Scripts -->
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
			<script type="text/javascript" src="{{ timestamped_asset($script) }}"></script>
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
						<span class="app-name d-flex align-items-center"><span>{{ config('app.name') }}</span></span>
					</a>
				</h1>

				<ul class="user-options">
					<li data-title="{{ trans('theme::admin.open-close menu') }}">
						<a href="#nav" class="hamburger toggle-menu">
							<span class="fa fa-step-forward" aria-hidden="true"></span>
							<span class="fa fa-step-backward" aria-hidden="true"></span><!--
							--><span class="menu-text">{{ trans('theme::admin.open-close menu') }}</span>
						</a>
					</li>
				</ul>

				<nav id="nav" role="navigation" class="admin-navigation">
					<div class="inner-wrap">
						<ul id="adminmenu">
							<li><a href="{{ route('admin.dashboard.index') }}" class="icon-dashboard"><span class="menu-text">Dashboard</span></a></li>
						</ul>
					</div>
				</nav><!-- / .main-navigation -->

				<ul class="user-options">
					@if (Auth::check())
						<li data-title="{{ trans('theme::admin.toggle theme') }}">
							<a id="mode"
								data-api="{{ route('api.users.update', ['id' => auth()->user()->id]) }}"
								data-mode="{{ auth()->user()->facet('theme.admin.mode', 'light') == 'light' ? 'dark' : 'light' }}"
								data-error="{{ trans('theme::admin.mode error') }}"
								href="{{ request()->url() }}?theme.admin.mode={{ auth()->user()->facet('theme.admin.mode', 'light') == 'light' ? 'dark' : 'light' }}">
								<span class="fa fa-sun-o" aria-hidden="true"></span>
								<span class="fa fa-moon-o" aria-hidden="true"></span><!--
								--><span class="menu-text">{{ trans('theme::admin.toggle theme') }}</span>
							</a>
						</li>
						<li data-title="{{ trans('theme::admin.account') }}">
							<a href="{{ route('admin.users.show', ['id' => auth()->user()->id]) }}">
								<span class="fa fa-user" aria-hidden="true"></span><span class="menu-text">{{ trans('theme::admin.account') }}</span>
							</a>
						</li>
						<li data-title="{{ trans('theme::admin.logout') }}">
							<a class="logout" href="{{ route('logout') }}">
								<span class="fa fa-power-off" aria-hidden="true"></span><span class="menu-text">{{ trans('theme::admin.logout') }}</span>
							</a>
						</li>
					@else
						@if (app('request')->input('hidemainmenu'))
							<li class="disabled" data-title="{{ trans('theme::admin.login') }}">
								<span class="login">
									<span class="fa fa-power-off" aria-hidden="true"></span><span class="menu-text">{{ trans('theme::admin.login') }}</span>
								</span>
							</li>
						@else
							<li data-title="{{ trans('theme::admin.login') }}">
								<a class="login" href="{{ route('login') }}">
									<span class="fa fa-power-off" aria-hidden="true"></span><span class="menu-text">{{ trans('theme::admin.login') }}</span>
								</a>
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
							{{ trans('global.warn javascript required') }}
						</noscript>
				</main>
				@include('partials.footer')
			</div><!-- / #wrap -->
		</div><!-- / #container-main -->
	</body>
</html>
@endif