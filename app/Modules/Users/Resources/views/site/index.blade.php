@extends('layouts.master')

@section('content')
<h2>{!! config('users::users.account') !!}</h2>

<form action="{{ url('site.users.index') }}" method="post" name="adminForm" id="adminForm">

	My info

	<?php
	foreach ($user->sessions as $session)
	{
		echo $session->id . ' ' . $session->ip_address;
		if ($session->id == session()->id)
		{
			echo 'current';
		}
	}
	?>

	@csrf
</form>

@stop