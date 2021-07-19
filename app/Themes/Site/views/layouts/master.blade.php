<!DOCTYPE html>
<html dir="ltr" lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="no-js">
	<head>

		<!-- Metadata -->
		<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="csrf-token" content="{{ csrf_token() }}">
		<meta name="base-url" content="{{ rtrim(asset('/'), '/') }}">
		<meta name="api-token" content="{{ (Auth::user() ? Auth::user()->api_token : '') }}">
		<meta name="theme-color" content="#000000">
		<meta name="color-scheme" content="light dark">

		<title>{{ config('app.name') }}@hasSection('title') - @yield('title')@endif</title>

		@hasSection('meta')
			@yield('meta')
		@else
			<meta name="description" content="Halcyon asset provisioner." />
			<meta name="keywords" content="Halcyon, HPC, asset provisioning" />
		@endif
		@stack('meta')

		<!-- Styles -->
<?php
		$styles = array(
			'modules/core/vendor/bootstrap/bootstrap.min.css' => 'rel="stylesheet" type="text/css"',
			'modules/core/vendor/jquery-ui/jquery-ui.min.css' => 'rel="stylesheet" type="text/css"',
			//'themes/site/css/font-awesome-css.min.css' => 'rel="stylesheet" type="text/css"',
			'themes/site/css/site.css' => 'rel="stylesheet" type="text/css"',
		);
		if (!app('isAdmin') && Auth::check()):
			$styles['themes/site/css/admin.css'] = 'rel="stylesheet" type="text/css"';
		endif;

		foreach ($styles as $css => $attrs):
			$attrs = $attrs ?: 'rel="stylesheet" type="text/css" media="all"';
			$sfx = (substr($css, 0, 4) != 'http' ? '?v=' . filemtime(public_path() . '/' . $css) : '');
			?>
		<link {!! $attrs !!} href="{{ asset($css . $sfx) }}" />
<?php
		endforeach;
		?>
		@stack('styles')
		@yield('styles')

		<!-- Scripts: Global -->
<?php
		$scripts = array(
			'modules/core/vendor/jquery/jquery.min.js',
			'modules/core/vendor/bootstrap/bootstrap.bundle.min.js', // Ths needs to be included before jquery-ui
			'modules/core/vendor/jquery-ui/jquery-ui.min.js',
			'themes/site/js/site.js',
		);
		foreach ($scripts as $script):
			?>
		<script src="{{ asset($script . '?v=' . filemtime(public_path() . '/' . $script)) }}"></script>
<?php
		endforeach;
		?>

		<!-- Scripts: Extension specific -->
		@stack('scripts')
		@yield('scripts')

	</head>
	<body>
		@widget('top')
		@if (app()->has('impersonate') && app('impersonate')->isImpersonating())
			<div class="notice-banner admin text-center">
				<div class="alert alert-info">
					You are impersonating {{ auth()->user()->name }}. <a href="{{ route('impersonate.leave') }}">Exit</a>
				</div>
			</div>
		@endif
		<header>
			<div class="container">
				<div class="row">
					<div class="logo">
						<h1>
							<a href="{{ route('home') }}">
								{{ config('app.name') }}
							</a>
						</h1>
					</div>
				</div>
				<div class="login">
					<ul>
						@if (Auth::check())
							<li><a href="{{ route('site.users.account') }}">{{ Auth::user()->name }}</a> &nbsp;|&nbsp; <a href="{{ route('logout') }}">{{ trans('theme::site.logout') }}</a></li>
						@else
							<li><a href="{{ route('login') }}" class="btn btn-secondary btn-inverse"><span class="fa fa-lock" aria-hidden="true"></span> {{ trans('theme::site.login') }}</a></li>
						@endif
					</ul>
				</div>
			</div>
		</header>

		<main id="content">
			@include('partials.notifications')

			@yield('content')
		</main>

		<footer id="footer">
			@widget('footer')
		</footer>

		@widget('bottom')
	</body>
</html>
