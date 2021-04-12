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

		<meta name="description" content="Information Technology at Purdue (ITaP) Research Computing provides advanced computational resources and services to support Purdue faculty and staff researchers." />
		<meta name="keywords" content="Purdue University, RCAC, Research Computing, Information Technology at Purdue, ITaP" />

		<!-- Styles -->
		<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/bootstrap/bootstrap.min.css?v=' . filemtime(public_path() . '/modules/core/vendor/bootstrap/bootstrap.min.css')) }}" />
		<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/prism/prism.css?v=' . filemtime(public_path() . '/modules/core/vendor/prism/prism.css')) }}" />
		<?php /*<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/swagger/swagger-ui.css?v=' . filemtime(public_path() . '/modules/core/vendor/swagger/swagger-ui.css')) }}" />*/ ?>
		<style>
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
			-webkit-overflow-scrolling: touch;
		}
		html {
			line-height: 1.15;
			-ms-text-size-adjust: 100%;
			-webkit-text-size-adjust:100%;
			font-family: sans-serif;
		}

		body {
			color: #000;
			/*background-color: #f5f5f5;*/
			font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica Neue,Arial,Noto Sans,sans-serif,Apple Color Emoji,Segoe UI Emoji,Segoe UI Symbol,Noto Color Emoji;
			font-size: 0.8rem;
			font-weight: 400;
			line-height: 1.5;
			color: #212529;
		}
		.docs-sidebar {
			background: #f9f9f9;
			border-right: 1px solid rgba(148,151,155,0.2);
height: 100%;
position: fixed;
overflow-y: auto;
max-width: 270px;
min-width: 270px;
overflow-x: hidden;
}
.docs-sidebar+.docs-main {
position: relative;
overflow: hidden;
margin-left: 270px;
}
.docs-content {
}
.docs-sidebar ul {
	list-style: none;
}
.docs-sidebar-tree ul {
	margin-left: 1em;
}
.docs-sidebar-tree a {
position: relative;
display: block;
font-size: 1em;
font-weight: 500;
color: inherit;
text-decoration: none;
user-select: none;
padding: 8px;
padding-left: 1.5rem;
line-height: 1;
}
.docs-sidebar-tree a:before {
content: "";
display: inline-block;
width: 1.2em;
height: 1.2em;

background-position: 0 0;
background-repeat: no-repeat;
margin-right: 0.5em;
transition: opacity 100ms;
opacity: 0.3;
vertical-align: bottom;
background: url("data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2216%22%20height%3D%2216%22%20viewBox%3D%220%200%2016%2016%22%3E%3Cpath%20fill%3D%22%23565B73%22%20d%3D%22M8.3%201H1v14h12V5.6L8.3%201zM4%2010h6v1H4v-1zm0-2h6v1H4V8zm0-2h4v1H4V6z%22%2F%3E%3C%2Fsvg%3E");
/*background: url("data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2216%22%20height%3D%2216%22%20viewBox%3D%220%200%2016%2016%22%3E%3Cpath%20fill%3D%22%23565B73%22%20d%3D%22M4.4%209l.2-2H2V6h2.7L5%203h1l-.3%203h2L8%203h1l-.3%203H11v1H8.6l-.2%202H11v1H8.3L8%2013H7l.3-3h-2L5%2013H4l.3-3H2V9h2.4zm1%200h2l.2-2h-2l-.2%202z%22%2F%3E%3C%2Fsvg%3E");*/
}
.docs-sidebar-tree .folder>a:before {
	background: url("data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2216%22%20height%3D%2216%22%20viewBox%3D%220%200%2016%2016%22%3E%3Cpath%20fill%3D%22%23008CFF%22%20d%3D%22M7%204h7v10H0V2h7v2zM1+3v1h5V3H1z%22%2F%3E%3C%2Fsvg%3E");
}
.docs-sidebar-tree .folder.active>a:before {
	background-image: url("data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2216%22%20height%3D%2216%22%20viewBox%3D%220%200%2016%2016%22%3E%3Cpath%20fill%3D%22%23008CFF%22%20opacity%3D%22.3%22%20d%3D%22M7%204h7v10H0V2h7v2zM1%203v1h5V3H1z%22%2F%3E%3Cpath%20fill%3D%22%23008CFF%22%20fill-rule%3D%22evenodd%22%20clip-rule%3D%22evenodd%22%20d%3D%22M14%2014H0l2-7h14l-2%207z%22%2F%3E%3C%2Fsvg%3E");
	opacity: 1;
}
.docs-sidebar-tree .folder ul {
	display: none;
}
.docs-sidebar-tree .folder.active ul {
	display: block;
}
.docs-sidebar-header {
position: relative;
display: flex;
align-items: center;
justify-content: space-between;
height: 5rem;
padding: 1.5rem;
padding-right: 0;
border-bottom: 1px solid rgba(148,151,155,0.2);
z-index: 0;
min-width: 270px;
margin: 0 0 2em 0;
}
.docs-sidebar-header h2 {
	font-size: 1.5em;
}
.docs-sidebar-header {
/*position: fixed;*/
border-right: 1px solid rgba(148,151,155,0.2);
z-index: 1;
max-width: 270px;
background-color: #F9F9F9;
}


.swagger-ui .opblock {
    margin: 0 0 15px;
    border: 1px solid rgba(148,151,155,0.2);
    /*border-radius: 4px;
    box-shadow:0 0 3px rgba(0, 0, 0, .19);*/
}

.swagger-ui .opblock .tab-header {
    display: flex;
    flex:1
}

.swagger-ui .opblock .tab-header .tab-item {
    padding: 0 40px;
    cursor:pointer
}

.swagger-ui .opblock .tab-header .tab-item:first-of-type {
    padding:0 40px 0 0
}

.swagger-ui .opblock .tab-header .tab-item.active h4 span {
    position:relative
}

.swagger-ui .opblock .tab-header .tab-item.active h4 span:after {
    position: absolute;
    bottom: -15px;
    left: 50%;
    width: 120%;
    height: 4px;
    content: "";
    transform: translateX(-50%);
    background:grey
}
/*
.swagger-ui .opblock.is-open .opblock-summary {
    border-bottom:1px solid #000
}
*/
.swagger-ui .opblock .opblock-section-header {
    display: flex;
    align-items: center;
    padding: 8px 20px;
    min-height: 50px;
    background: hsla(0, 0%, 100%, .8);
    box-shadow:0 1px 2px rgba(0, 0, 0, .1)
}

.swagger-ui .opblock .opblock-section-header > label {
    font-size: 12px;
    font-weight: 700;
    display: flex;
    align-items: center;
    margin: 0 0 0 auto;
    font-family: sans-serif;
    color:#3b4151
}

.swagger-ui .opblock .opblock-section-header > label > span {
    padding:0 10px 0 0
}

.swagger-ui .opblock .opblock-section-header h4 {
    font-size: 1em;
    flex: 1;
    margin: 0;
    /*font-family: sans-serif;
    color:#3b4151*/
}

.swagger-ui .opblock .opblock-summary-method {
    font-size: 1em;
    font-weight: 700;
    min-width: 7em;
    padding: 6px 15px;
    text-align: center;
    /*border-radius: 3px;*/
    background: #000;
    text-shadow: 0 1px 0 rgba(0, 0, 0, .1);
    font-family: sans-serif;
    color:#fff
}
.swagger-ui .opblock .opblock-summary-path {
    flex-shrink: 0;
    min-width: 40%;
    max-width: calc(100% - 110px - 15rem)
}

.swagger-ui .opblock .opblock-summary-path__deprecated {
    text-decoration:line-through
}

.swagger-ui .opblock .opblock-summary-operation-id {
    font-size:14px
}

.swagger-ui .opblock .opblock-summary-description {
    flex: 1 1 auto;
    word-break: break-word;
    font-family: sans-serif;
    color:#3b4151
}
/*@media (max-width: 768px) {
	.swagger-ui .opblock .opblock-summary-description {
		flex: none;
		display: block;
		width: 100%;
	}
}*/


.swagger-ui .opblock .opblock-summary {
    display: flex;
    align-items: center;
    /*padding: 5px;*/
    cursor:pointer;
    background-color: #f5f5f5;
}
.swagger-ui .opblock .opblock-summary-operation-id,
.swagger-ui .opblock .opblock-summary-path,
.swagger-ui .opblock .opblock-summary-path__deprecated {
    font-size: 1.2em;
    display: flex;
    align-items: center;
    word-break: break-word;
    padding: 0 10px;
    font-family: monospace;
    font-weight: 600;
    color:#3b4151
}
.swagger-ui .opblock .opblock-section {
	padding: 2em;
}

@media (max-width: 768px) {
    .swagger-ui .opblock .opblock-summary-operation-id,
    .swagger-ui .opblock .opblock-summary-path,
    .swagger-ui .opblock .opblock-summary-path__deprecated {
        font-size: 0.85em;
    }
}

.swagger-ui .opblock .opblock-summary-path {
    flex-shrink: 0;
    max-width:calc(100% - 110px - 15rem)
}

.swagger-ui .opblock.opblock-get .opblock-summary-method {
    background:#61affe
}
.swagger-ui .opblock.opblock-patch .opblock-summary-method {
    background:#50e3c2
}
.swagger-ui .opblock.opblock-delete .opblock-summary-method {
    background:#f93e3e
}
.swagger-ui .opblock.opblock-put .opblock-summary-method {
    background:#fca130
}
.swagger-ui .opblock.opblock-post .opblock-summary-method {
    background:#49cc90;
    /*background: #fff;
    color: #49cc90;
    border: 2px solid #49cc90;*/
}

.swagger-ui .opblock .opblock-body {
	display: none;
}
.swagger-ui .opblock.is-open .opblock-body {
	display: block;
}

.docs-api-tag {
text-transform: uppercase;
letter-spacing: 0.1em;
font-size: 0.625rem;
font-weight: 500;
line-height: 1;
display: inline-block;
padding: 0.75em 1em;
margin-right: 0.75em;
margin-bottom: 0.75em;
box-shadow: inset 0 0 0 1px rgba(148,151,155,0.2);
border-radius: 2px;
transition: background-color 100ms;
}
.docs-api-param-query {
	color: green;
}
.docs-api-param-body {
	color: blue;
}
/*.docs-api-param-type {
font-style: italic;
}*/
pre {
	padding: 1em;
	background-color: #152748;
	box-shadow: inset 0 -2px 0 -1px rgba(148,151,155,0.2);
	color: white;
}
.required {
	font-size: 85%;
	display: inline-block;
	background-color: #c00;
	color: #fff;
	border-radius: 0.25em;
	padding: 0.2em 0.5em;
}
.twlo-code pre.line-numbers {
padding-left: 4.5rem !important;
counter-reset: linenumber;
}
		</style>
		@yield('styles')
		@stack('styles')

		<!-- Scripts -->
		<script type="text/javascript" src="{{ asset('modules/core/vendor/prism/prism.js?v=' . filemtime(public_path() . '/modules/core/vendor/prism/prism.js')) }}"></script>
		<script type="text/javascript">
			var base_url = '{!! request()->getBaseUrl() !!}',
				Halcyon = {};

			/**
			 * Check if an element has the specified class name
			 *
			 * @param   el         The element to test
			 * @param   className  The class to test for
			 * @return  bool
			 */
			Halcyon.hasClass = function(el, className) {
				return el.classList ? el.classList.contains(className) : new RegExp('\\b'+ className+'\\b').test(el.className);
			}

			/**
			 * Add a class to an element
			 *
			 * @param   el         The element to add the class to
			 * @param   className  The class to add
			 * @return  bool
			 */
			Halcyon.addClass = function(el, className) {
				if (el.classList) {
					el.classList.add(className);
				} else if (!Halcyon.hasClass(el, className)) {
					el.className += ' ' + className;
				}
			}

			/**
			 * Remove a class from an element
			 *
			 * @param   el         The element to remove the class from
			 * @param   className  The class to remove
			 * @return  bool
			 */
			Halcyon.removeClass = function(el, className) {
				if (el.classList) {
					el.classList.remove(className);
				} else {
					el.className = el.className.replace(new RegExp('\\b'+ className+'\\b', 'g'), '');
				}
			}

			document.addEventListener('DOMContentLoaded', function() {
				var i;

				// Add event listeners to toolbar buttons
				var summary = document.getElementsByClassName('opblock-summary');
				for (i = 0; i < summary.length; i++)
				{
					summary[i].addEventListener('click', function(e){
						e.preventDefault();

						if (Halcyon.hasClass(this.parentNode, 'is-open')) {
							Halcyon.removeClass(this.parentNode, 'is-open');
						} else {
							Halcyon.addClass(this.parentNode, 'is-open');
						}
					});
				}

				var nodes = document.getElementsByClassName('node');
				for (i = 0; i < nodes.length; i++)
				{
					nodes[i].addEventListener('click', function(e){
						e.preventDefault();

						if (Halcyon.hasClass(this.parentNode, 'active')) {
							Halcyon.removeClass(this.parentNode, 'active');
						} else {
							var nds = document.getElementsByClassName('node');
							for (i = 0; i < nds.length; i++)
							{
								Halcyon.removeClass(nds[i].parentNode, 'active');
							}

							Halcyon.addClass(this.parentNode, 'active');
						}
					});
				}
			});
		</script>
		@yield('scripts')
		@stack('scripts')
	</head>
	<body>
		<header class="sr-only">
			<!-- logo and Tagline -->
			<div class="top">
				<h1>API Documentation</h1>
			</div><!-- / .top -->
		</header>

		<nav class="docs-sidebar">
			<div class="docs-sidebar-header">
				<h2>API Documentation</h2>
			</div>
			<ul class="docs-sidebar-tree">
				@foreach ($modules as $mod)
				<li class="folder<?php if ($mod->getLowerName() == $module) { echo ' active'; } ?>">
					<a class="node" href="{{ route('api.' . $mod->getLowerName() . '.index') }}">{{ trans($mod->getLowerName() . '::' . $mod->getLowerName() . '.module name') }}</a>
					@if (isset($documentation['sections'][$mod->getLowerName()]))
						<ul>
						@foreach ($documentation['sections'][$mod->getLowerName()] as $controller => $endpoints)
							<li>
								<a href="{{ route('api.' . $mod->getLowerName() . '.index') }}#operations-tag-{{ $controller }}">
									{{ $endpoints['name'] ? $endpoints['name'] : trans($mod->getLowerName() . '::' . $mod->getLowerName() . '.' . $controller) }}
								</a>
							</li>
						@endforeach
						</ul>
					@endif
				</li>
				@endforeach
			</ul>
		</nav>

		<main class="docs-main swagger-ui">
			<div class="docs-content m-5">
				<?php
				if ($module):
					$active = $documentation['sections'][$module];

					foreach ($active as $controller => $endpoints):
						if (empty($endpoints)):
							continue;
						endif;
					?>
				<section class="mb-5">
					<h3 class="opblock-tag" id="operations-tag-{{ $controller }}" data-tag="{{ $controller }}" data-is-open="false">
						{{ $endpoints['name'] ? $endpoints['name'] : trans($mod->getLowerName() . '::' . $mod->getLowerName() . '.' . $controller) }}
					</h3>

					@if ($endpoints['description'])
						<p>{{ $endpoints['description'] }}</p>
					@endif

					<?php
				foreach ($endpoints['endpoints'] as $endpoint):
					if (!$endpoint['method']):
						continue;
					endif;

					$key = $endpoint['_metadata']['module'] . '-' . $endpoint['_metadata']['method'];
					?>
					<div class="doc-section endpoint" id="{{ $key }}">

						<div class="opblock opblock-{{ strtolower($endpoint['method']) }}" id="{{ $key }}">
							<div class="opblock-summary opblock-summary-post">
								@if ($endpoint['method'])
									<span class="opblock-summary-method">{{ $endpoint['method'] }}</span>
								@endif
								@if ($endpoint['uri'])
									<span class="opblock-summary-path" data-path="{{ $endpoint['uri'] }}">
										{!! preg_replace('/(\{[^}]+\})/', "<code>$1</code>", $endpoint['uri']) !!}
									</span>
								@endif
								@if ($endpoint['name'])
									<div class="opblock-summary-description">{{ $endpoint['name'] }}</div>
								@endif
								@if (isset($endpoint['authorization']) && $endpoint['authorization'])
									<span class="authorization__btn locked">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="14" height="14"><path d="M400 224h-24v-72C376 68.2 307.8 0 224 0S72 68.2 72 152v72H48c-26.5 0-48 21.5-48 48v192c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V272c0-26.5-21.5-48-48-48zm-104 0H152v-72c0-39.7 32.3-72 72-72s72 32.3 72 72v72z"/></svg>
									</span>
								@endif
							</div>

							<div class="opblock-body">
								@if ($endpoint['description'])
									<div class="opblock-description-wrapper">
										<div class="opblock-description">
											<p>{{ $endpoint['description'] }}</p>
										</div>
									</div>
								@endif

								<div class="opblock-section" id="{{ $key }}-params">
									<div class="opblock-section-header">
										<div class="tab-header">
											<h4 class="opblock-title">{{ trans('core::docs.parameters') }}</h4>
										</div>
										<!-- <div class="try-out"><button class="btn try-out__btn">Try it out </button></div> -->
									</div>
									<div class="parameters-container">
										<div class="table-container">
											@if (count($endpoint['parameters']) > 0)
												<table class="table">
													<caption class="sr-only">{{ trans('core::docs.parameters') }}</caption>
													<thead>
														<tr>
															<th scope="col">{{ trans('core::docs.name') }}</th>
															<th scope="col">{{ trans('core::docs.in') }}</th>
															<th scope="col">{{ trans('core::docs.type') }}</th>
															<th scope="col">{{ trans('core::docs.default') }}</th>
															<th scope="col">{{ trans('core::docs.description') }}</th>
															<th scope="col">{{ trans('core::docs.accepted values') }}</th>
														</tr>
													</thead>
													<tbody>
														@foreach ($endpoint['parameters'] as $param)
															<tr>
																<td>
																	<code><span class="docs-api-param-name">{{ $param['name'] }}</span></code>
																</td>
																<td>
																	@if (isset($param['in']) && $param['in'])
																		<span class="docs-api-tag docs-api-param-{{ isset($param['in']) ? $param['in'] : '' }}">{{ $param['in'] }}</span>
																	@endif
																</td>
																<td>
																	@if (isset($param['schema']['type']) && $param['schema']['type'])
																		<span class="docs-api-tag docs-api-param-type">{{ $param['schema']['type'] }}</span>
																	@endif
																	@if (isset($param['schema']['format']) && $param['schema']['format'])
																		<br /><small>Format: {{ $param['schema']['format'] }}</small>
																	@endif
																	@if (isset($param['schema']['example']) && $param['schema']['example'])
																		<br /><small>Example: {{ $param['schema']['example'] }}</small>
																	@endif
																</td>
																<td>
																	<code class="nohighlight">{{ (isset($param['schema']['default']) && !is_null($param['schema']['default'])) ? $param['schema']['default'] : 'null' }}</code>
																</td>
																<td>
																	@if ($param['required'])
																		<span class="required">{{ trans('global.required') }}</span>
																	@endif
																	{{ $param['description'] }}
																</td>
																<td>
																	@if (isset($param['schema']['enum']))
																		<code class="nohighlight">{!! implode('</code>, <code class="nohighlight">', $param['schema']['enum']) !!}</code>
																	@endif
																</td>
															</tr>
														@endforeach
													</tbody>
												</table>
											@else
												<p class="alert alert-info">{{ trans('global.none') }}</p>
											@endif
										</div>
									</div>
								</div>

								@if (isset($endpoint['response']) && $endpoint['response'])
								<div class="opblock-section">
									<div class="opblock-section-header">
										<div class="tab-header">
											<h4 class="opblock-title">{{ trans('core::docs.response') }}</h4>
										</div>
									</div>
									<div class="response-container">
										<table class="table">
											<caption class="sr-only">{{ trans('core::docs.response codes') }}</caption>
											<thead>
												<tr>
													<th scope="col">{{ trans('core::docs.code') }}</th>
													<th scope="col">{{ trans('core::docs.description') }}</th>
													<th scope="col">{{ trans('core::docs.example') }}</th>
												</tr>
											</thead>
											<tbody>
												@foreach ($endpoint['response'] as $code => $response)
													<tr>
														<td>
															<code><span class="docs-api-param-name">{{ $code }}</span></code>
														</td>
														<td>
															{{ isset($response->description) ? $response->description : '' }}
														</td>
														<td>
														@if (isset($response->content))
															@foreach ($response->content as $mime => $example)
																<code>{{ $mime }}</code>:
																<pre><code class="language-json">{{ json_encode($example->example, JSON_PRETTY_PRINT) }}</code></pre>
															@endforeach
														@endif
														</td>
													</tr>
												@endforeach
											</tbody>
										</table>
									</div>
								</div>
								@endif
							</div>
						</div>
					</div>
				<?php
				endforeach;
				?>
			</section>
			<?php
					endforeach;
				endif;
				?>
			</div>
		</main>

		<footer>
			fdsasd
		</footer>
	</body>
</html>
