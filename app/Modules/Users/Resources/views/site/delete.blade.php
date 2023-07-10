@extends('layouts.master')

@php
$active = $sections->firstWhere('active', '=', true);
@endphp

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ timestamped_asset('modules/core/vendor/select2/css/select2.css') }}" />
@endpush

@push('scripts')
<script src="{{ timestamped_asset('modules/core/vendor/select2/js/select2.min.js') }}"></script>
<script src="{{ timestamped_asset('modules/users/js/request.js') }}"></script>
@endpush

@section('content')
@include('users::site.admin', ['user' => $user])
<div class="row">
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
	<div class="contentInner">
		<h2>{{ trans('users::users.request access') }}</h2>

		<form method="post" action="{{ route('site.users.account.delete') }}">
			<p>This action cannot be undone. This will permanently delete the account and remove all associations.</p>
			<div class="form-group">
				<label for="confirmdelete">Please type "<strong>{{ $user->name }}</strong>" to confirm.</label>
				<input type="text" name="confirmdelete" id="confirmdelete" class="form-control" required value="" />
			</div>
			<div class="form-group">
				<input type="submit" class="btn btn-danger" value="I understand the consequences, delete this account" />
			</div>
			@csrf
		</form>
	</div>
</div>
</div>
@stop