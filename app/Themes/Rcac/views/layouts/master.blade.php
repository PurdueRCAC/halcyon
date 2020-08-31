<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="no-js">
	<head>
		<!-- Metadata -->
		<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="csrf-token" content="{{ csrf_token() }}">
		<meta name="base-url" content="{{ rtrim(asset('/'), '/') }}">
		<meta name="api-token" content="{{ (Auth::user() ? Auth::user()->api_token : '') }}">

		<title>ITaP Research Computing @hasSection('title') - @yield('title') @endif</title>

		@hasSection('meta')
			@yield('meta')
		@else
			<meta name="description" content="Information Technology at Purdue (ITaP) Research Computing provides advanced computational resources and services to support Purdue faculty and staff researchers." />
			<meta name="keywords" content="Purdue University, RCAC, Research Computing, Information Technology at Purdue, ITaP" />
		@endif
		@stack('meta')

		<!-- Styles -->
		<?php
		$styles = array(
			'themes/Rcac/js/common/jquery-ui-1.12.1/themes/base/jquery-ui.min.css',
			'themes/Rcac/css/jquerytimepicker_min.css',
			'themes/Rcac/css/font-awesome-css.min.css',
			'themes/Rcac/css/college.css',
			'themes/Rcac/css/content.css',
		);
		foreach ($styles as $css):
			?>
			<link rel="stylesheet" type="text/css" media="all" href="{{ asset($css . '?v=' . filemtime(public_path() . '/' . $css)) }}" />
			<?php
		endforeach;
		?>
		@if (Request::is('/'))
			<link rel="stylesheet" type="text/css" media="all" href="{{ asset('themes/Rcac/css/homepage.css?v=' . filemtime(public_path() . '/themes/Rcac/css/homepage.css')) }}" />
		@endif
		@yield('styles')
		@stack('styles')

		<!--[if IE 9]>
			<link rel="stylesheet" type="text/css" media="screen" href="{{ asset('css/browser/ie9.css') }}" />
		<![endif]-->
		<!--[if lt IE 9]>
			<script type="text/javascript" src="{{ asset('js/html5.js') }}"></script>
			<link rel="stylesheet" type="text/css" media="screen" href="{{ asset('css/browser/ie8.css') }}" />
		<![endif]-->

		<!-- Scripts -->
		<script type="text/javascript">
			var base_url = '{!! request()->getBaseUrl() !!}';
		</script>
		<?php
		$scripts = array(
			'themes/Rcac/js/jquery-3.3.1.min.js',
			'themes/Rcac/js/common/jquery-ui-1.12.1/jquery-ui.min.js',
			'themes/Rcac/js/css_browser_selector.js',
			'themes/Rcac/js/modernizr-1.5.min.js',
			'themes/Rcac/js/bootstrap.min.js',
			//'themes/Rcac/js/common/jquery-ui-1.12.1/jquery-ui.min.js',
			'themes/Rcac/js/google_jquery_link_tracking.js',
		);
		foreach ($scripts as $script):
			?>
			<script type="text/javascript" src="{{ asset($script . '?v=' . filemtime(public_path() . '/' . $script)) }}"></script>
			<?php
		endforeach;

		foreach (scandir(public_path() . '/themes/Rcac/js/common/') as $file)
		{
			if (preg_match("/^.*?\.js$/", $file))
			{
				echo "\t\t" . '<script type="text/javascript" src="' . asset('themes/Rcac/js/common/' . $file . '?v=' . filemtime(public_path() . '/themes/Rcac/js/common/' . $file)) . '"></script>' . "\n";
			}
		}
		?>
		@yield('scripts')
		@stack('scripts')

		<script>
		// this makes the menu's first link clickable
		jQuery(function($) {
			$('.navbar .dropdown').hover(
				function() {
					$(this).find('.dropdown-menu').first().stop(true, true).delay(10).slideDown();
				},
				function() {
					$(this).find('.dropdown-menu').first().stop(true, true).delay(10).slideUp();
				}
			);

			$('.navbar .dropdown > a').on('click', function(e){
				location.href = this.href;
			});
		});
		</script>
	</head>
	<body>
		@if (Auth::check() && Auth::user()->can('manage'))
			@include('partials.admin-bar')
		@endif
		<header>
			<div class="navbar navbar-inverse goldbar" role="navigation">
				<div class="container">
					<div class="navbar-header">
						<button class="navbar-toggle left" data-target=".gold" data-toggle="collapse" type="button">
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span> Quick Links
						</button>
						<button class="navbar-toggle search right" data-target="#search" data-toggle="collapse" type="button">
							<i class="fa fa-search fa-lg"></i> 
						</button>
					</div><!-- / .navbar-header -->
					<div class="collapse navbar-collapse right search" id="search">
						<ul class="nav navbar-nav navbar-right">
							<li class="dropdown">
								<a class="dropdown-toggle" data-toggle="dropdown" title="Search Button" href="#"><i class="fa fa-search fa-lg"></i> Search</a>
								<ul class="dropdown-menu">
									<li>
										<div class="form-group">
											<script async src="https://cse.google.com/cse.js?cx=017690826183710227054:mjxnqnpskjk"></script>
											<div class="gcse-searchbox-only" data-resultsUrl="https://www.purdue.edu/purdue/search.html" data-queryParameterName="q"></div>
										</div>
									</li>
								</ul>
							</li>
						</ul>
					</div><!-- / .search -->
					<div class="collapse navbar-collapse gold">
						<ul class="nav navbar-nav information">
							<li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" title="Info Menu" href="#">Find Info For <b class="caret"></b></a>
								<p class="hide">Find Info For</p>
								<ul class="dropdown-menu">
									<li><a href="http://www.purdue.edu/purdue/academics/">Academics</a></li>
									<li><a href="http://www.purdue.edu/purdue/admissions/">Admissions</a></li>
									<li><a href="http://www.purdue.edu/purdue/current_students/">Current Students</a></li>
									<li><a href="http://www.purdue.edu/purdue/athletics/">Athletics</a></li>
									<li><a href="http://www.purdue.edu/purdue/about/">About</a></li>
									<li><a href="http://www.purdue.edu/purdue/careers/">Careers</a></li>
									<li><a href="http://www.purdue.edu/purdue/prospective_students/">Prospective Students</a></li>
									<li><a href="http://www.purdue.edu/purdue/research/">Research and Partnerships</a></li>
								</ul>
							</li>
						</ul>
						<p class="hide">Quick Links</p>
						<ul class="nav navbar-nav right quicklinks">
							<li><a href="http://www.purdue.edu/purdue/admissions/">Apply</a></li>
							<li><a href="http://www.purdue.edu/newsroom/">News</a></li>
							<li><a href="http://www.purdue.edu/president/">President</a></li>
							<li><a href="http://www.purdueofficialstore.com/">Shop</a></li>
							<li><a href="http://www.purdue.edu/visit/">Visit</a></li>
							<li><a href="http://www.purdue.edu/purdue/giveNow.html">Give</a></li>
							<li><a href="http://www.purdue.edu/ea/">Emergency</a></li>
						</ul>
					</div><!-- / .gold -->
				</div>
			</div><!-- / .goldbar -->

			<!-- logo and Tagline -->
			<div class="top">
				<div class="container">
					<div class="row">
						<div class="logo col-lg-2 col-md-3 col-sm-3 col-xs-12">
							<h1 class="sr-only">Purdue University</h1>
							<a class="svgLinkContainer" href="https://www.purdue.edu">
								<object class="svgContainer" data="https://www.purdue.edu/purdue/images/PU-H.svg" type="image/svg+xml">
									<img alt="Purdue University" src="https://www.purdue.edu/purdue/images/PU-H.png" />
								</object>
							</a>
						</div>
						<div class="department col-lg-9 col-md-9 col-sm-9 col-xs-12">
							<a href="{{ url()->to('/') }}" title="{{ config('site-name') }}">
								Information Technology<span class="tagline">Research Computing</span>
							</a>
						</div>
					</div>
					<div class="login">
						<ul>
							@if (Auth::check())
								<li><a href="{{ url('/account/myinfo/') }}">{{ Auth::user()->name }}</a> &nbsp;|&nbsp; <a href="{{ route('logout') }}">{{ trans('theme::rcac.logout') }}</a></li>
							@else
								<li><a href="{{ route('login') }}" class="btn btn-default"><i class="fa fa-lock" aria-hidden="true"></i> {{ trans('theme::rcac.login') }}</a></li>
							@endif
							<li><a href="{{ url('/help') }}" class="btn btn-info"><i class="fa fa-question-circle" aria-hidden="true"></i> {{ trans('theme::rcac.get help') }}</a></li>
						</ul>
					</div>
				</div>
			</div><!-- / .top -->

			<nav class="navbar navbar-inverse blackbar" aria-label="Main Menu">
				<div class="container">
					<div class="navbar-header">
						<button class="navbar-toggle" data-target=".black" data-toggle="collapse" type="button">
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span> {{ trans('theme::rcac.menu') }}
						</button>
					</div>
					<div class="collapse navbar-collapse black">
						@widget('mainmenu')
					</div>
				</div>
			</nav><!-- / .blackbar -->


		@if (!Request::is('/'))
			@widget('breadcrumbs')
		@endif
		</header>
		<main id="content">
			@include('partials.notifications')

			<div class="container">
				<div class="row contentPage">
				@if (app('widget')->count('left'))
					<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
						@widget('left')
					</div><!-- /.sidenav end -->

					<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
				@else
					<!--<div class="contentInner col-lg-12 col-md-12 col-sm-12 col-xs-12"> -->
				@endif

						@yield('content')

					@if (app('widget')->count('left'))
					</div><!-- /.contentInner -->
					@endif
				</div>
			</div>
		</main>

		<footer id="footer">
			@widget('footer')
		</footer>
	</body>
</html>
