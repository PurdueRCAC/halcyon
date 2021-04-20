@extends('layouts.master')

@section('content')
	<div class="error-page">
		<div class="error-header">
			<h2>404</h2>
		</div>
		<div class="error-body">
			<h3><i class="fa fa-warning text-yellow"></i> {{ trans('global.error 404 title') }}</h3>

			<p>Sorry, but we could not find that file or page.</p>

			<p>A <a href="https://www.purdue.edu/purdue/search/results.html?cx=017690826183710227054%3Amjxnqnpskjk&cof=FORID%3A11&amp;q=site%3Awww.rcac.purdue.edu+{{ app('request')->path() }}&amp;sa=Search&amp;siteurl=www.purdue.edu%2F">search for "{{ app('request')->path() }}"</a> may find what you were looking for.</p>

			<p>If you still can't find what you were looking for please contact <a href="mailto:rcac-help@purdue.edu">rcac-help@purdue.edu</a>.</p>
		</div>
		<!-- /.error-content -->
	</div>
@stop
