<!DOCTYPE html>
<html dir="ltr" lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="no-js">
	<head>
		<!--
		         @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@%
		        @@@,//////////////////////////////,@@@
		       @@@,////////////////////////////////,@@%
		      @@@@@@@@,////////,@@@@@@@@@@,///////,@@@
		          @@@,///////////////////////////,@@@
		         @@@,/////////////////////////,#@@@
		   @@@@@@@@,////////,@@@@@@@@@@@@@@@@@@@#
		  @@@,//////////////////,@@@
		 @@@,//////////////////,@@@
		@@@@@@@@@@@@@@@@@@@@@@@@@@
		-->

		<!-- Metadata -->
		<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="csrf-token" content="{{ csrf_token() }}" />
		<meta name="base-url" content="{{ rtrim(asset('/'), '/') }}" />
		<meta name="api-token" content="{{ (Auth::user() ? Auth::user()->api_token : '') }}" />
		<meta name="theme-color" content="#000000" />

		<title>{{ config('app.name') }}@hasSection('title') - @yield('title')@endif</title>

		@hasSection('meta')
			@yield('meta')
		@else
			<meta name="description" content="Information Technology at Purdue (ITaP) Research Computing provides computational resources and services to support Purdue faculty and staff researchers." />
			<meta name="keywords" content="Purdue University, RCAC, Research Computing, Information Technology at Purdue, ITaP" />
		@endif
		@stack('meta')

		<!-- Apple touch icons -->
		<link rel="apple-touch-icon" href="{{ asset('themes/rcac/images/icon-iphone.png') }}">
		<link rel="apple-touch-icon" href="{{ asset('themes/rcac/images/icon-iphone.png') }}" sizes="76x76" />
		<link rel="apple-touch-icon" href="{{ asset('themes/rcac/images/icon-ipad.png') }}" sizes="76x76" />
		<link rel="apple-touch-icon" href="{{ asset('themes/rcac/images/icon-iphone-retina.png') }}" sizes="120x120" />
		<link rel="apple-touch-icon" href="{{ asset('themes/rcac/images/icon-ipad-retina.png') }}" sizes="152x152" />

		<!-- Styles -->
		<link rel="preload" as="style" href="https://use.typekit.net/ghc8hdz.css" />
<?php
		$styles = array(
			//'https://use.typekit.net/ghc8hdz.css' => 'rel="preload" as="style"',
			'https://use.typekit.net/ghc8hdz.css' => 'rel="stylesheet" type="text/css"',
			//'themes/rcac/js/common/jquery-ui-1.12.1/themes/base/jquery-ui.min.css' => 'rel="stylesheet" type="text/css"',
			'modules/core/vendor/jquery-ui/jquery-ui.min.css' => 'rel="stylesheet" type="text/css"',
			'themes/rcac/css/font-awesome-css.min.css' => 'rel="stylesheet" type="text/css"',
			'themes/rcac/css/college.css' => 'rel="stylesheet" type="text/css"',
			'themes/rcac/css/content.css' => 'rel="stylesheet" type="text/css"'
			//'themes/rcac/css/site.css' => 'rel="stylesheet" type="text/css"',
		);
		if (!app('isAdmin') && Auth::check()):
			$styles['themes/rcac/css/admin.css'] = 'rel="stylesheet" type="text/css"';
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
		<script type="text/javascript">
			var base_url = '{!! request()->getBaseUrl() !!}';
		</script>
<?php
		$scripts = array(
			'modules/core/vendor/jquery/jquery.min.js',
			'modules/core/vendor/bootstrap/bootstrap.bundle.min.js', // Ths needs to be included before jquery-ui
			'modules/core/vendor/jquery-ui/jquery-ui.min.js',
			'themes/rcac/js/common/common.js',
			'themes/rcac/js/google_jquery_link_tracking.js',
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
			<nav class="navbar navbar-expand-lg goldbar" aria-label="Purdue University Quick Links">
				<div class="container">
					<div class="navbar-header navbar-toggler">
						<button class="navbar-toggle left" data-target=".gold" data-toggle="collapse" aria-controls="pu-quicklinks" type="button">
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span> Quick Links
						</button>
						<button class="navbar-toggle search right" data-target="#search" data-toggle="collapse" aria-controls="search" type="button">
							<span class="fa fa-search fa-lg" aria-hidden="true"></span><span class="sr-only">Search</span>
						</button>
					</div><!-- / .navbar-header -->
					
					<div class="collapse navbar-collapse gold justify-content-between">
						<ul class="nav navbar-nav information">
							<li class="dropdown">
								<a class="dropdown-toggle" data-toggle="dropdown" title="Info Menu" href="#find-info-for">Find Info For <span class="caret"></span></a>
								<p class="hide">Find Info For</p>
								<ul class="dropdown-menu" id="find-info-for">
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
						<ul class="nav navbar-nav right quicklinks" id="pu-quicklinks">
							<li><a href="http://www.purdue.edu/purdue/admissions/">Apply</a></li>
							<li><a href="http://www.purdue.edu/newsroom/"><span class="sr-only">Purdue University </span>News</a></li>
							<li><a href="http://www.purdue.edu/president/">President</a></li>
							<li><a href="http://www.purdueofficialstore.com/">Shop</a></li>
							<li><a href="http://www.purdue.edu/visit/">Visit</a></li>
							<li><a href="http://www.purdue.edu/purdue/giveNow.html">Give</a></li>
							<li><a href="http://www.purdue.edu/ea/">Emergency</a></li>
						</ul>
					</div><!-- / .gold -->

					<div class="collapse navbar-collapse right search" id="search">
						<ul class="nav navbar-nav navbar-right">
							<li class="dropdown">
								<a class="dropdown-toggle" data-toggle="dropdown" title="Search Button" href="#search"><span class="fa fa-search fa-lg" aria-hidden="true"></span><span class="sr-only">Search</span></a>
								<ul class="dropdown-menu dropdown-menu-md-right">
									<li>
										<div class="form-group">
											<?php
											/*
											The Google search appliance uses tables for layouts. This causes issues for accessibility.
											Most of the structure is reproduced here with DIVs, using the same classnames and IDs.

											<script async src="https://cse.google.com/cse.js?cx=017690826183710227054:mjxnqnpskjk"></script>
											<div class="gcse-searchbox-only" data-resultsUrl="https://www.purdue.edu/purdue/search.html" data-queryParameterName="q"></div>
											*/
											?>
											<div id="___gcse_0">
												<div class="gsc-control-searchbox-only gsc-control-searchbox-only-en" dir="ltr">
													<form class="gsc-search-box gsc-search-box-tools" method="get" action="https://www.purdue.edu/purdue/search.html" accept-charset="utf-8">
														<div class="gsc-input">
															<div class="gsc-input-box" id="gsc-iw-id1">
																<div id="gs_id50" class="gstl_50 gsc-input">
																	<div id="gs_tti50" class="gsib_a">
																		<input autocomplete="off" type="text" size="10" class="form-control gsc-input" name="q" title="search" id="gsc-i-id1" dir="ltr" spellcheck="false">
																	</div>
																	<div  class="gsib_b">
																		<div class="gsst_b" id="gs_st50" dir="ltr">
																			<button type="reset" class="gsst_a" title="Clear search box">
																				<span class="gscb_a" id="gs_cb50" aria-hidden="true">×</span>
																			</button>
																		</div>
																	</div>
																</div>
															</div>
														</div>
														<div class="gsc-search-button sr-only">
															<button type="submit" class="gsc-search-button gsc-search-button-v2">
																<svg width="13" height="13" viewBox="0 0 13 13"><title>search</title><path d="m4.8495 7.8226c0.82666 0 1.5262-0.29146 2.0985-0.87438 0.57232-0.58292 0.86378-1.2877 0.87438-2.1144 0.010599-0.82666-0.28086-1.5262-0.87438-2.0985-0.59352-0.57232-1.293-0.86378-2.0985-0.87438-0.8055-0.010599-1.5103 0.28086-2.1144 0.87438-0.60414 0.59352-0.8956 1.293-0.87438 2.0985 0.021197 0.8055 0.31266 1.5103 0.87438 2.1144 0.56172 0.60414 1.2665 0.8956 2.1144 0.87438zm4.4695 0.2115 3.681 3.6819-1.259 1.284-3.6817-3.7 0.0019784-0.69479-0.090043-0.098846c-0.87973 0.76087-1.92 1.1413-3.1207 1.1413-1.3553 0-2.5025-0.46363-3.4417-1.3909s-1.4088-2.0686-1.4088-3.4239c0-1.3553 0.4696-2.4966 1.4088-3.4239 0.9392-0.92727 2.0864-1.3969 3.4417-1.4088 1.3553-0.011889 2.4906 0.45771 3.406 1.4088 0.9154 0.95107 1.379 2.0924 1.3909 3.4239 0 1.2126-0.38043 2.2588-1.1413 3.1385l0.098834 0.090049z"></path></svg>
															</button>
														</div>
													</form>
												</div>
											</div>
										</div>
									</li>
								</ul>
							</li>
						</ul>
					</div><!-- / .search -->
				</div>
			</nav><!-- / .goldbar -->

			<!-- logo and Tagline -->
			<div class="top">
				<div class="container">
					<div class="row">
						<div class="logo col-lg-3 col-md-3 col-sm-3 col-xs-12">
							<h1 class="sr-only">Purdue University</h1>
							<a class="svgLinkContainer" href="https://www.purdue.edu" title="Purdue University">
								<object class="svgContainer" data="{{ asset('themes/rcac/images/PU-H.svg') }}" type="image/svg+xml" aria-label="Purdue University" title="Purdue University" role="img">
									<img alt="Purdue University" src="{{ asset('themes/rcac/images/PU_SIG_Logo_RGB__PU-H-Full-RGB_Black_white.png') }}" height="45" width="243" />
								</object>
							</a>
						</div>
						<div class="department col-lg-9 col-md-9 col-sm-9 col-xs-12">
							<a href="{{ route('home') }}" title="{{ config('app.name') }}">
								Information Technology<span class="tagline">Research Computing</span>
							</a>
						</div>
					</div>
					<div class="login">
						<ul>
							@if (Auth::check())
								<li><a href="{{ route('site.users.account') }}">{{ Auth::user()->name }}</a></li>
								<li><a href="{{ route('logout') }}">{{ trans('theme::rcac.logout') }}</a></li>
								<li>
									<?php
									event($event = new App\Modules\Users\Events\UserNotifying(Auth::user()));
									?>
									<div class="notifications-dropdown dropdown<?php if (count($event->notifications)) { echo ' has-notices'; } ?>">
										<button class="btn dropdown-togle" type="button" id="dropdownMenu2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
											<span class="fa fa-bell" aria-hidden="true"></span>
										</button>
										<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
											@if (count($event->notifications))
												@foreach ($event->notifications as $item)
													<div class="dropdown-item">
														<div class="dropdown-item-content">
														@if ($item->title)
															<small class="dropdown-item-title">{{ $item->title }}</small>
														@endif
														{!! $item->content !!}
														</div>
													</div>
												@endforeach
											@else
												<div class="dropdown-item text-center text-muted">
													You have no notifications.
												</div>
											@endif
										</div>
									</div>
								</li>
							@else
								<li><a href="{{ route('login') }}" class="btn btn-secondary btn-inverse"><span class="fa fa-lock" aria-hidden="true"></span> {{ trans('theme::rcac.login') }}</a></li>
							@endif
							<li><a href="{{ route('page', ['uri' => 'help']) }}" class="btn btn-info"><span class="fa fa-question-circle" aria-hidden="true"></span> {{ trans('theme::rcac.get help') }}</a></li>
						</ul>
					</div>
				</div>
			</div><!-- / .top -->

			@widget('header')

			<nav class="navbar navbar-light navbar-expand-lg blackbar" aria-label="Main Menu">
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
			<div class="footer">
				<div class="container">
					<div class="row panel-group" id="accordion">
						<div class="panel panel-default">
							<div class="panel-heading">
								<h3 class="panel-title"><a class="collapsed" data-parent="#accordion" data-toggle="collapse" href="#footerone">Portal <span class="fa fa-plus right" aria-hidden="true"></span><span class="fa fa-minus right" aria-hidden="true"></span></a></h3>
							</div>
							<div class="panel-collapse collapse" id="footerone">
								<div class="panel-body">
									<ul>
										<li><a href="https://mypurdue.purdue.edu/">MyPurdue</a></li>
										<li><a href="https://one.purdue.edu/">OneCampus Portal</a></li>
										<li><a href="https://www.purdue.edu/employeeportal">Employee Portal</a></li>
									</ul>
								</div>
							</div>
						</div>
						<div class="panel panel-default">
							<div class="panel-heading">
								<h3 class="panel-title"><a class="collapsed" data-parent="#accordion" data-toggle="collapse" href="#footertwo"><span class="sr-only">Purdue </span>Services <span class="fa fa-plus right" aria-hidden="true"></span><span class="fa fa-minus right" aria-hidden="true"></span></a></h3>
							</div>
							<div class="panel-collapse collapse" id="footertwo">
								<div class="panel-body">
									<ul>
										<li><a href="https://www.itap.purdue.edu/">Information Technology</a></li>
										<li><a href="https://www.lib.purdue.edu/">Libraries</a></li>
										<li><a href="https://www.itap.purdue.edu/directory/">Directory</a></li>
									</ul>
								</div>
							</div>
						</div>
						<div class="panel panel-default">
							<div class="panel-heading">
								<h3 class="panel-title"><a class="collapsed" data-parent="#accordion" data-toggle="collapse" href="#footerthree">Communication <span class="fa fa-plus right" aria-hidden="true"></span><span class="fa fa-minus right" aria-hidden="true"></span></a></h3>
							</div>
							<div class="panel-collapse collapse" id="footerthree">
								<div class="panel-body">
									<ul>
										<li><a href="https://outlook.office.com/">Outlook</a></li>
										<li><a href="https://portal.office.com/">Office 365</a></li>
										<li><a href="https://www.purdue.edu/newsroom/purduetoday/">Purdue Today</a></li>
									</ul>
								</div>
							</div>
						</div>
						<div class="panel panel-default">
							<div class="panel-heading">
								<h3 class="panel-title"><a class="collapsed" data-parent="#accordion" data-toggle="collapse" href="#footerfour">University <span class="fa fa-plus right" aria-hidden="true"></span><span class="fa fa-minus right" aria-hidden="true"></span></a></h3>
							</div>
							<div class="panel-collapse collapse" id="footerfour">
								<div class="panel-body">
									<ul>
										<li><a href="http://www.purdue.edu/research/">Research &amp; Partnerships</a></li>
										<li><a href="http://www.purdue.edu/purdue/careers/">Careers</a></li>
										<li><a href="http://www.purdue.edu/diversity-inclusion/">Diversity &amp; Inclusion</a></li>
									</ul>
								</div>
							</div>
						</div> 
						<div class="motto panel panel-default">
							<div class="taglineContainer">
								<div class="tagline">
									<svg class="horizontal" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 249.7 45.3" style="enable-background:new 0 0 249.7 45.3;">
										<style>
											.st0{fill:#FFFFFF;}
											.st1{fill:#CFB991;}
											.st2{fill:#9D9795;}
										</style>
										<title>PU-H-Full-Rev-RGB</title>
										<g>
											<path class="st0" d="M97.4,3.8L94.6,3V1h10.9c7.1,0,9.7,2.3,9.7,7.6c0,5-2.9,7.6-8.5,7.6h-4.4v8l2.8,0.8V27H94.5v-1.9l2.9-0.8V3.8z
													M102.3,3.8v10.1l5.2-0.4c1.3-0.6,2.6-1.4,2.6-4.6c0-2.5-0.1-5.1-4.9-5.1L102.3,3.8z"></path>
											<path class="st0" d="M155.8,16h-3.5v8.4l2.8,0.8V27h-10.7v-1.9l2.9-0.8V3.8L144.5,3V1h11.4c6.8,0,9.2,2.4,9.2,7
												c0,3.6-1.4,5.4-4.6,6.7l4.8,9.4l3.1,1V27h-7.2L155.8,16z M152.3,3.8v9.9l5.3-0.4c1.7-0.8,2.5-2.2,2.5-4.9c0-2.9-0.9-4.7-3.9-4.7
												L152.3,3.8z"></path>
											<path class="st0" d="M170.6,25.2l2.8-0.8V3.8L170.6,3V1h12.6c7.1,0,11.3,4.3,11.3,12.7c0,9.5-4.3,13.3-11.8,13.3h-12.1V25.2z
													M178.4,3.8v20.6h3.7c2.6,0,7-0.4,7-9.8c0-7.4-1.3-10.8-7-10.8L178.4,3.8z"></path>
											<path class="st0" d="M105.5,40.6c0,3.2-1.8,4.3-4.8,4.3s-4.8-1.8-4.8-4.3v-6.3l-1.4-0.4v-1.2h5.5v1.2l-1.3,0.4v6.3
												c0,1.6,0.6,2.5,2.4,2.5c0.9,0,2.4-0.3,2.4-2.6v-6.3l-1.4-0.4v-1.2h4.8v1.2l-1.4,0.4L105.5,40.6z"></path>
											<path class="st0" d="M115.1,37.3v5.8l1.3,0.4v1.2h-4.7v-1.2l1.4-0.4v-8.3l-0.6-0.7l-0.8-0.3v-1.3h3.1l5.9,7.4v-5.8l-1.4-0.4v-1.2
												h4.7v1.2l-1.4,0.4v10.5h-1.6L115.1,37.3z"></path>
											<path class="st0" d="M134.1,43.2l1.4,0.4v1.2h-5.6v-1.2l1.4-0.4v-8.9l-1.4-0.4v-1.2h5.6v1.2l-1.4,0.4V43.2z"></path>
											<path class="st0" d="M209,43.2l1.4,0.4v1.2h-5.6v-1.2l1.4-0.4v-8.9l-1.4-0.4v-1.2h5.6v1.2l-1.4,0.4L209,43.2z"></path>
											<path class="st0" d="M148.2,44.7h-2.6l-3.8-10.5l-1.4-0.4v-1.2h5.9v1.2l-1.4,0.4l2.6,7.5l2.6-7.5l-1.4-0.4v-1.2h4.8v1.2l-1.4,0.4
												L148.2,44.7z"></path>
											<path class="st0" d="M159.2,34.3l-1.4-0.4v-1.2h9.5l0.1,3.7H166l-0.7-2.1h-3.2v3.4h3.1v1.7h-3.1v3.8h3.2l1-2.1h1.5l-0.2,3.7h-9.7
												v-1.2l1.4-0.3V34.3z"></path>
											<path class="st0" d="M179.1,39.8h-1v3.4l1.4,0.4v1.2h-5.6v-1.2l1.4-0.4v-8.9l-1.4-0.4v-1.2h5.5c3.4,0,4.4,1.3,4.4,3.4
												c0,1.3-0.3,2.6-2,3.2l2,3.9l1.7,0.5v1.1h-4.1L179.1,39.8z M178.1,34.3v4.2l1.9-0.2c0.6-0.5,1-1.3,1-2.1c0-1.2-0.3-2-1.6-2H178.1z"></path>
											<path class="st0" d="M190.4,41h1.7l0.4,1.7c0.8,0.5,1.7,0.7,2.6,0.7c0.1,0,0.3,0,0.4,0c0.5-0.3,0.7-0.9,0.8-1.5
												c0-2.8-5.8-1.6-5.8-5.7c0-1.9,1.7-3.7,4.6-3.7c1.1,0,2.3,0.3,3.3,0.8v3.2h-1.6l-0.6-2c-0.6-0.2-1.3-0.4-2-0.4c-0.1,0-0.2,0-0.3,0
												c-0.5,0.3-0.8,0.8-0.8,1.4c0,2.4,5.7,1.6,5.7,5.6c0,2.2-2,3.8-4.6,3.8c-1.3,0-2.6-0.4-3.7-1.1L190.4,41z"></path>
											<path class="st0" d="M220,34.3h-1.6l-0.7,2.1h-1.5v-3.8h10.6v3.8h-1.5l-0.7-2.1h-1.7v8.9l1.4,0.4v1.2h-5.6v-1.2l1.4-0.4V34.3z"></path>
											<path class="st0" d="M236.2,40.1l-3.2-5.8l-1.4-0.4v-1.2h5.9v1.2l-1.4,0.4l2,3.9l2-3.9l-1.4-0.4v-1.2h4.8v1.2l-1.4,0.4l-3.2,5.8
												v3.1l1.4,0.4v1.2h-5.6v-1.2l1.4-0.4V40.1z"></path>
											<polygon class="st0" points="241.4,19.5 239.5,24.3 231.4,24.3 231.4,15 236,15 236.6,17.6 238.6,17.6 238.6,9.7 236.6,9.7 
												236,12.3 231.4,12.3 231.4,3.8 239.2,3.8 240.6,8.8 243.5,8.8 243.3,1 223.6,1 223.6,3 226.4,3.8 226.4,24.4 223.5,25.2 223.5,27 
												243.7,27 244.1,19.5 	"></polygon>
											<path class="st0" d="M139.5,18.1c0,6.6-3.2,9.3-9.6,9.3c-5.9,0-10.2-2.4-10.2-8.7V3.8L116.8,3V1h10.6v2l-2.8,0.8v14.9
												c0,3.8,1.7,5.4,5.9,5.4c2.9,0,5.5-1.7,5.5-5.7V3.8L133.1,3V1h9.2v2l-2.8,0.8V18.1z"></path>
											<path class="st0" d="M218.5,18.1c0,6.6-3.2,9.3-9.6,9.3c-5.9,0-10.3-2.4-10.3-8.7V3.8L195.8,3V1h10.6v2l-2.8,0.8v14.9
												c0,3.8,1.7,5.4,5.9,5.4c2.9,0,5.5-1.7,5.5-5.7V3.8L212.2,3V1h9.2v2l-2.8,0.8V18.1z"></path>
											<path class="st1" d="M44.4,44.8L50,31.6h9.3c13.4,0,18.5-5.5,22.1-13.8c1.3-3.1,3.5-8.2,0.6-12.6c-2.9-4.4-9-4.8-13.3-4.8H19.3
												l-7,16.3h8.9l-5,11.7h-9l-7,16.4H44.4z"></path>
											<path class="st2" d="M79.4,6.9c-1.6-2.4-5-3.5-10.8-3.5H21.3L17,13.7h8.9l-7.5,17.7h-9L5,41.7h37.4l4.4-10.3h-9.1l1.2-2.9h20.4
												c13.1,0,16.6-5.6,19.3-12C80,13.2,81.3,9.7,79.4,6.9 M45.3,13.7h15c2.1,0,1.8,1,1.5,1.7c-0.8,1.8-2.6,2.9-4.8,2.9H43.3L45.3,13.7z"></path>
											<path d="M68.7,5H22.4l-3,7.1h8.9L19.4,33h-9l-3,7.1h34l3-7.1h-9.1l2.6-6h21.4c12.3,0,15.4-5.2,17.8-11S81,5,68.7,5 M57,19.9H41
												l3.3-7.7h16c2.8,0,4,1.5,2.9,3.9C62.2,18.4,59.8,19.9,57,19.9"></path>
											<path class="st0" d="M246.9,44.7c-1.4,0-2.6-1.2-2.6-2.6c0-1.4,1.2-2.6,2.6-2.6c1.4,0,2.6,1.2,2.6,2.6c0,0,0,0,0,0
												C249.5,43.5,248.3,44.7,246.9,44.7z M246.9,40.1c-1.1,0-2.1,0.9-2.1,2.1c0,1.1,0.9,2.1,2.1,2.1c1.1,0,2.1-0.9,2.1-2.1
												C249,41,248,40.1,246.9,40.1L246.9,40.1z"></path>
											<path class="st0" d="M246.6,42.3v1h-0.5v-2.4h1.1c0.5,0,0.8,0.3,0.8,0.7c0,0.2-0.1,0.5-0.4,0.6c0.1,0,0.3,0.2,0.3,0.6v0.1
												c0,0.2,0,0.3,0,0.5h-0.5c0-0.2-0.1-0.4,0-0.5v0c0-0.3-0.1-0.4-0.5-0.4L246.6,42.3z M246.6,41.9h0.4c0.3,0,0.4-0.1,0.4-0.3
												s-0.1-0.3-0.4-0.3h-0.4V41.9z"></path>
										</g>
									</svg>
									<svg class="vertical" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" viewBox="0 0 203.8 132" style="enable-background:new 0 0 203.8 132;">
										<style>
											.v-st0{fill:#CFB991;}
											.v-st1{fill:#9D9795;}
											.v-st2{fill:#FFFFFF;}
										</style>
										<title>PU-V-Full-Rev-RGB</title>
										<g>
											<path class="v-st0" d="M102.5,58.7l7.3-17.3H122c17.6,0,24.3-7.2,28.9-18.1c1.7-4,4.6-10.7,0.8-16.5S140,0.5,134.3,0.5H69.7
												l-9.1,21.4h11.7l-6.5,15.3H54l-9.1,21.4L102.5,58.7z"></path>
											<path class="v-st1" d="M148.4,9.1c-2.1-3.1-6.5-4.6-14.1-4.6h-62L66.5,18h11.7l-9.9,23.2H56.6l-5.7,13.5h49l5.7-13.5H93.8l1.6-3.8
												H122c17.1,0,21.7-7.4,25.3-15.7C149.2,17.3,150.8,12.7,148.4,9.1 M103.7,18h19.6c2.7,0,2.3,1.4,1.9,2.2c-1,2.3-3.4,3.8-6.3,3.8
												h-17.8L103.7,18z"></path>
											<path d="M134.3,6.6H73.6l-4,9.4h11.7L69.7,43.3H58l-4,9.4h44.5l4-9.4H90.6l3.4-7.9h28.1c16.1,0,20.1-6.8,23.4-14.4
												S150.4,6.5,134.3,6.6 M118.9,26H98l4.3-10.1h21c3.7,0,5.2,1.9,3.9,5.1S122.6,26,118.9,26"></path>
											<path class="v-st2" d="M4,78l-3.7-1.1v-2.6h14.3c9.3,0,12.8,3,12.8,10c0,6.6-3.8,10-11.1,10h-5.8v10.6l3.7,1.1v2.5h-14v-2.5l3.8-1.1
												V78z M10.5,77.9v13.2l6.8-0.5c1.7-0.8,3.4-1.8,3.4-6.1c0-3.2-0.1-6.6-6.4-6.6L10.5,77.9z"></path>
											<path class="v-st2" d="M80.7,93.9H76v11l3.7,1.1v2.5h-14v-2.5l3.8-1.1V78l-3.7-1.1v-2.6h14.9c8.9,0,12.1,3.1,12.1,9.2
												c0,4.7-1.8,7.1-6,8.8l6.3,12.3l4.1,1.3v2.5h-9.5L80.7,93.9z M76,77.9v13l6.9-0.5c2.2-1.1,3.2-2.8,3.2-6.4c0-3.8-1.2-6.1-5.1-6.1
												L76,77.9z"></path>
											<path class="v-st2" d="M100,105.9l3.7-1V78l-3.7-1.1v-2.6h16.5c9.3,0,14.8,5.6,14.8,16.7c0,12.5-5.6,17.4-15.5,17.4H100V105.9z
													M110.2,77.9v27h4.9c3.4,0,9.1-0.5,9.1-12.9c0-9.7-1.7-14.1-9.1-14.1L110.2,77.9z"></path>
											<path class="v-st2" d="M14.7,126.2c0,4.1-2.4,5.7-6.3,5.7s-6.3-2.3-6.3-5.7v-8.3l-1.8-0.6v-1.6h7.3v1.6l-1.8,0.6v8.3
												c0,2.1,0.8,3.3,3.2,3.3c1.1,0,3.1-0.4,3.1-3.4v-8.2l-1.8-0.6v-1.6h6.2v1.6l-1.8,0.5V126.2z"></path>
											<path class="v-st2" d="M27.2,121.9v7.6L29,130v1.6h-6.1V130l1.8-0.5v-10.8l-0.8-1l-1.1-0.3v-1.7h4l7.7,9.7v-7.6l-1.8-0.5v-1.6h6.1
												v1.6l-1.8,0.5v13.8h-2.1L27.2,121.9z"></path>
											<path class="v-st2" d="M52.2,129.5L54,130v1.6h-7.3V130l1.8-0.5v-11.7l-1.8-0.5v-1.6H54v1.6l-1.8,0.5L52.2,129.5z"></path>
											<path class="v-st2" d="M150.4,129.5l1.8,0.5v1.6h-7.3V130l1.8-0.5v-11.7l-1.8-0.5v-1.6h7.3v1.6l-1.8,0.5V129.5z"></path>
											<path class="v-st2" d="M70.6,131.6h-3.4l-5-13.7l-1.8-0.6v-1.6h7.7v1.6l-1.8,0.6l3.3,9.8l3.4-9.8l-1.8-0.6v-1.6h6.2v1.6l-1.8,0.6
												L70.6,131.6z"></path>
											<path class="v-st2" d="M85.1,117.8l-1.8-0.5v-1.6h12.5l0.1,4.9h-2l-1-2.8h-4.1v4.5h4.1v2.3h-4.1v5H93l1.3-2.8h1.9l-0.3,4.8H83.3V130
												l1.8-0.4V117.8z"></path>
											<path class="v-st2" d="M111.1,125.1h-1.4v4.4l1.8,0.5v1.6h-7.3V130l1.8-0.5v-11.7l-1.8-0.5v-1.6h7.2c4.4,0,5.8,1.7,5.8,4.4
												c0,1.8-0.4,3.4-2.6,4.2l2.5,5.2l2.2,0.7v1.5h-5.4L111.1,125.1z M109.8,117.8v5.6l2.5-0.2c0.8-0.7,1.3-1.7,1.2-2.8
												c0-1.6-0.4-2.6-2-2.6H109.8z"></path>
											<path class="v-st2" d="M126,126.7h2.2l0.5,2.2c1,0.6,2.2,0.9,3.3,0.9c0.2,0,0.3,0,0.5,0c0.6-0.4,1-1.2,1-1.9c0-3.7-7.6-2.1-7.6-7.5
												c0-2.5,2.3-4.9,6-4.9c1.5,0,3,0.4,4.3,1.1v4.2h-2.1l-0.8-2.7c-0.8-0.3-1.7-0.5-2.6-0.5c-0.1,0-0.3,0-0.4,0c-0.6,0.4-1,1.1-1,1.9
												c0,3.2,7.5,2,7.5,7.4c0,2.9-2.6,4.9-6.1,4.9c-1.7,0-3.4-0.5-4.8-1.4L126,126.7z"></path>
											<path class="v-st2" d="M164.7,117.9h-2.1l-0.9,2.8h-2v-5h13.8v5h-1.9l-0.9-2.8h-2.2v11.6l1.8,0.5v1.6h-7.3V130l1.8-0.5V117.9z"></path>
											<path class="v-st2" d="M186,125.5l-4.2-7.6l-1.8-0.6v-1.6h7.7v1.6l-1.8,0.6l2.6,5.2l2.6-5.2l-1.8-0.6v-1.6h6.3v1.6l-1.8,0.6l-4.2,7.6
												v4.1l1.8,0.5v1.6h-7.3V130l1.8-0.5L186,125.5z"></path>
											<polygon class="v-st2" points="192.7,98.5 190.3,104.9 179.6,104.9 179.6,92.6 185.7,92.6 186.4,96 189.1,96 189.1,85.7 186.4,85.7 
												185.7,89 179.6,89 179.6,77.9 189.8,77.9 191.8,84.5 195.5,84.5 195.2,74.3 169.4,74.3 169.4,76.9 173.1,78 173.1,104.9 
												169.4,105.9 169.4,108.4 195.7,108.4 196.3,98.5 	"></polygon>
											<path class="v-st2" d="M59.2,96.7c0,8.7-4.1,12.2-12.6,12.2c-7.8,0-13.4-3.1-13.4-11.3V78l-3.7-1.1v-2.6h13.9v2.6L39.6,78v19.6
												c0,4.9,2.3,7.1,7.7,7.1c3.7,0,7.2-2.2,7.2-7.5V78l-3.7-1.1v-2.6h12v2.6L59.2,78V96.7z"></path>
											<path class="v-st2" d="M162.8,96.7c0,8.7-4.1,12.2-12.6,12.2c-7.8,0-13.4-3.1-13.4-11.3V78l-3.7-1.1v-2.6h13.9v2.6l-3.7,1.1v19.6
												c0,4.9,2.3,7.1,7.7,7.1c3.7,0,7.2-2.2,7.2-7.5V78l-3.7-1.1v-2.6h12v2.6l-3.7,1.1V96.7z"></path>
											<path class="v-st2" d="M200,131.5c-1.9,0-3.4-1.5-3.4-3.4c0-1.9,1.5-3.4,3.4-3.4c1.9,0,3.4,1.5,3.4,3.4l0,0
												C203.4,130,201.9,131.5,200,131.5z M200,125.4c-1.5,0-2.7,1.2-2.7,2.7s1.2,2.7,2.7,2.7c1.5,0,2.7-1.2,2.7-2.7l0,0
												C202.7,126.7,201.5,125.4,200,125.4L200,125.4L200,125.4z"></path>
											<path class="v-st2" d="M199.6,128.5v1.3h-0.7v-3.2h1.4c0.7,0,1.1,0.4,1.1,0.9c0,0.3-0.2,0.6-0.5,0.7c0.2,0.1,0.4,0.2,0.4,0.8v0.2
												c0,0.2,0,0.4,0,0.6h-0.7c-0.1-0.2-0.1-0.5-0.1-0.7v0c0-0.3-0.1-0.5-0.6-0.5L199.6,128.5z M199.6,127.9h0.6c0.4,0,0.5-0.1,0.5-0.4
												c0-0.2-0.2-0.4-0.5-0.4h-0.6L199.6,127.9z"></path>
										</g>
									</svg>
								</div><!-- / .tagline -->
							</div><!-- / .taglineContainer -->
							<div class="social">
								<a href="https://www.facebook.com/PurdueUniversity/" rel="noopener" target="_blank"><span class="sr-only">Facebook</span><span aria-hidden="true" class="fa fa-facebook"></span></a>

								<a href="https://twitter.com/lifeatpurdue" rel="noopener" target="_blank"><span class="sr-only">Twitter</span><span aria-hidden="true" class="fa fa-twitter"></span></a>

								<a href="https://www.youtube.com/user/PurdueUniversity" rel="noopener" target="_blank"><span class="sr-only">YouTube</span><span aria-hidden="true" class="fa fa-youtube"></span></a>

								<a href="https://www.instagram.com/lifeatpurdue/" rel="noopener" target="_blank"><span class="sr-only">Instagram</span><span aria-hidden="true" class="fa fa-instagram"></span></a>

								<a href="https://www.pinterest.com/lifeatpurdue/" rel="noopener" target="_blank"><span class="sr-only">Pinterest</span><span aria-hidden="true" class="fa fa-pinterest"></span></a>

								<a href="https://www.snapchat.com/add/lifeatpurdue" rel="noopener" target="_blank"><span class="sr-only">Snapchat</span><span aria-hidden="true" class="fa fa-snapchat-ghost"></span></a>

								<a href="https://www.linkedin.com/edu/purdue-university-18357" rel="noopener" target="_blank"><span class="sr-only">LinkedIn</span><span aria-hidden="true" class="fa fa-linkedin"></span></a>
							</div><!-- / .social -->
						</div><!-- / .motto -->
					</div><!-- / .row -->
				</div><!-- / .container -->
			</div><!-- / .footer -->
			<div class="bottom">
				<div class="container">
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<p>Purdue University, 610 Purdue Mall, West Lafayette, IN 47907, (765) 494-4600</p>
							<p><a href="http://www.purdue.edu/purdue/disclaimer.html"> &#169; {{ date("Y") }} Purdue University</a> | <a href="http://www.purdue.edu/purdue/ea_eou_statement.html">An equal access/equal opportunity university</a> | <a href="https://www.purdue.edu/purdue/about/integrity_statement.php">Integrity Statement</a> | <a href="https://www.purdue.edu/securepurdue/security-programs/copyright-policies/reporting-alleged-copyright-infringement.php">Copyright Complaints</a> | <a href="/about/contact/">Maintained by ITaP Research Computing</a></p>
							<?php /*<p>Contact Office of Marketing and Media at <a href="mailto:digital-marketing@groups.purdue.edu?subject=Accessibility%20Issue%20with%20Your%20Webpage">digital-marketing@groups.purdue.edu</a> for accessibility issues with this page | <a href="https://www.purdue.edu/disabilityresources/">Accessibility Resources</a> | <a href="https://www.purdue.edu/purdue/contact-us">Contact Us</a></p>*/ ?>
							<p>Contact Research Computing at <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a> for accessibility issues with this page | <a href="https://www.purdue.edu/disabilityresources/">Accessibility Resources</a> | <a href="https://www.purdue.edu/purdue/contact-us/index.php">Contact Purdue</a></p>
						</div>
					</div><!-- / .row -->
				</div><!-- / .container -->
			</div><!-- / .bottom -->
		</footer>
		@widget('bottom')
	</body>
</html>
