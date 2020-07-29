@extends('layouts.master')

@section('scripts')
<script>
	$(document).ready(function() {
		$('.reveal').on('click', function(e){
			$($(this).data('toggle')).toggleClass('hide');

			var text = $(this).data('text');
			$(this).data('text', $(this).html()); //.replace(/"/, /'/));
			$(this).html(text);
		});
	});
</script>
@stop

@section('title'){{ trans('users::users.courses') }}@stop

@section('content')

@include('users::site.admin', ['user' => $user])

<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	<div class="qlinks">
		<ul class="dropdown-menu">
			<li><a href="{{ route('site.users.account') }}">{{ trans('users::users.my accounts') }}</a></li>
			<li class="active"><a href="{{ route('site.users.account.groups') }}">{{ trans('users::users.my groups') }}</a></li>
			<li><a href="{{ route('site.users.account.quotas') }}">{{ trans('users::users.my quotas') }}</a></li>
			<li><a href="{{ route('site.orders.index') }}">{{ trans('users::users.my orders') }}</a></li>
		</ul>
	</div>
</div>

<div class="col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<div class="contentInner">
		@if (auth()->user()->can('create groups'))
			<a class="btn btn-default float-right" href="{{ route('site.users.account.groups') }}">
				<i class="fa fa-plus-circle"></i> {{ trans('global.create') }}
			</a>
		@endif

		<h2>{{ trans('users::users.sources') }}</h2>

		<div id="everything">
			<ul>
				@foreach ($courses as $course)
				<li>
					<a href="{{ route('site.users.account.course', ['course' => $course->id]) }}">
						{{ $course->classname }}
					</a>
				</li>
				@endforeach
			</ul>

		</div>

	</div>
</div>

@stop