<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="no-js">
	<head>
		<!-- Metadata -->
		<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="base-url" content="{{ rtrim(asset('/'), '/') }}">
		<meta name="api-token" content="{{ (Auth::user() ? Auth::user()->api_token : '') }}">

		<title>{{ config('app.name') }} - {{ trans('core::docs.api documentation') }}</title>

		<meta name="description" content="Information Technology at Purdue (ITaP) Research Computing provides advanced computational resources and services to support Purdue faculty and staff researchers." />
		<meta name="keywords" content="Purdue University, RCAC, Research Computing, Information Technology at Purdue, ITaP" />

		<!-- Styles -->
		<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/bootstrap/bootstrap.min.css?v=' . filemtime(public_path() . '/modules/core/vendor/bootstrap/bootstrap.min.css')) }}" />
		<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/prism/prism.css?v=' . filemtime(public_path() . '/modules/core/vendor/prism/prism.css')) }}" />
		<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/css/api.css?v=' . filemtime(public_path() . '/modules/core/css/api.css')) }}" />
		<?php /*<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/swagger/swagger-ui.css?v=' . filemtime(public_path() . '/modules/core/vendor/swagger/swagger-ui.css')) }}" />*/ ?>
		@yield('styles')
		@stack('styles')

		<!-- Scripts -->
		<script type="text/javascript" src="{{ asset('modules/core/vendor/prism/prism.js?v=' . filemtime(public_path() . '/modules/core/vendor/prism/prism.js')) }}"></script>
		<script type="text/javascript" src="{{ asset('modules/core/js/api.js?v=' . filemtime(public_path() . '/modules/core/js/api.js')) }}"></script>
		@yield('scripts')
		@stack('scripts')
	</head>
	<body>
		<header class="sr-only">
			<div class="top">
				<h1>{{ config('app.name') }}</h1>
			</div>
		</header>

		<nav class="docs-sidebar">
			<div class="docs-sidebar-header">
				<h2>
					{{ trans('core::docs.api documentation') }}
					@if (!empty($documentation['info']['version']))
						<span class="badge badge-info">{{ $documentation['info']['version'] }}</span>
					@endif
				</h2>
				<a href="#endpoints" class="navbar-toggle" data-target=".docs-sidebar-tree" data-toggle="collapse" type="button" title="{{ trans('core::docs.menu') }}">
					<span class="bar"></span>
					<span class="bar"></span>
					<span class="bar"></span>
					<span class="sr-only">{{ trans('core::docs.menu') }}</span>
				</a>
			</div>
			<ul class="docs-sidebar-tree" id="endpoints">
				@foreach ($modules as $mod)
				<li class="folder<?php if ($mod->getLowerName() == $module) { echo ' active'; } ?>">
					<a class="node" href="#{{ $mod->getLowerName() }}">{{ trans($mod->getLowerName() . '::' . $mod->getLowerName() . '.module name') }}</a>
					@if (isset($documentation['sections'][$mod->getLowerName()]))
						<ul>
						@foreach ($documentation['sections'][$mod->getLowerName()] as $controller => $endpoints)
							<li>
								<a class="node-endpoints" href="#{{ $mod->getLowerName() . '-' . strtolower($controller) }}">
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

		<main class="panel panel-v">
			@if (!empty($documentation['servers']))
				<div class="docs-info">
					@foreach ($documentation['servers'] as $server)
						<p><a href="{{ $server['url'] }}">{{ $server['url'] }}</a> - {{ $server['description'] }}</p>
					@endforeach
				</div>
			@endif
			<div class="docs-main swagger-ui">
				<div class="docs-content">
					@if (!empty($documentation['errors']))
						<div class="alert alert-danger">
							@foreach ($documentation['errors'] as $error)
								<p>{{ $error }}</p>
							@endforeach
						</div>
					@endif

					<?php
					$cls = '';
					if ($module):
						$cls = ' hide';
					endif;
					?>
					<div class="docs-collection{{ $cls }}">
						<section class="mb-5">
							<p>Choose a section to view available endpoints, parameters, and examples.</p>
						</section>
					</div>

					<?php
					foreach ($documentation['sections'] as $mod => $active):
						//$active = $documentation['sections'][$module];
						$cls = '';
						if ($mod != $module):
							$cls = ' hide';
						endif;
						?>
						<div class="docs-collection{{ $cls }}" id="{{ strtolower($mod) }}">
						<?php
						foreach ($active as $controller => $endpoints):
							if (empty($endpoints)):
								continue;
							endif;
							?>
							<section class="endpoints mb-5" id="{{ strtolower($mod . '-' . $controller) }}">
								<h3 class="opblock-tag" data-tag="{{ $controller }}" data-is-open="false">
									{{ $endpoints['name'] ? $endpoints['name'] : trans($mod . '::' . $mod . '.' . $controller) }}
								</h3>

								@if ($endpoints['description'])
									<p>{{ $endpoints['description'] }}</p>
								@endif

								<?php
								foreach ($endpoints['endpoints'] as $endpoint):
									if (!$endpoint['method']):
										continue;
									endif;

									$key = strtolower($endpoint['_metadata']['module'] . '-' . $endpoint['_metadata']['controller'] . '-' . $endpoint['_metadata']['method']);
									?>
									<div class="doc-section endpoint" id="{{ $key }}">

										<div class="opblock opblock-{{ strtolower($endpoint['method']) }}">
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
						?>
						</div>
						<?php
					endforeach;
					?>
				</div>
			</div>

			<footer>
				<section class="basement">
					<p class="copyright">
						{!! trans('core::docs.copyright', ['name' => config('app.name'), 'url' => url()->to('/api'), 'date' => gmdate("Y")]) !!}
					</p>
					<p class="promotion">
						{!! trans('core::docs.powered by', ['v' => 1]) !!}
					</p>
				</section>
			</footer>
		</main>

	</body>
</html>
