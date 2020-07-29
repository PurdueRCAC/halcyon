@extends('layouts.master')

@section('content')
<h2>{!! config('resources.name') !!}</h2>

<form action="{{ url('/resources') }}" method="post" name="adminForm" id="adminForm">

	My info

	<?php
	var_dump($user->sessions);
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