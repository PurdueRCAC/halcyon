@extends('layouts.master')

@php
$active = $sections->firstWhere('active', '=', true);
@endphp

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css?v=' . filemtime(public_path() . '/modules/core/vendor/select2/css/select2.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
<script src="{{ asset('modules/users/js/request.js?v=' . filemtime(public_path() . '/modules/users/js/request.js')) }}"></script>
@endpush

@section('content')
@include('users::site.admin', ['user' => $user])
<div class="row">
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	<h2>{{ $user->name }}</h2>

	<div class="qlinks">
		<ul class="nav flex-column">
			<li class="nav-item<?php if (!$active) { echo ' active'; } ?>">
				<a class="nav-link<?php if (!$active) { echo ' active'; } ?>" href="{{ auth()->user()->id != $user->id ? route('site.users.account', ['u' => $user->id]) : route('site.users.account') }}">{{ trans('users::users.my accounts') }}</a>
			</li>
			@foreach ($sections as $section)
				<li class="nav-item<?php if ($section['active']) { echo ' active'; } ?>">
					<a class="nav-link<?php if ($section['active']) { echo ' active'; } ?>" href="{{ $section['route'] }}">{!! $section['name'] !!}</a>
				</li>
			@endforeach
		</ul>
	</div>
</div>

<div class="col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<div class="contentInner">
		<h2>{{ trans('users::users.request access') }}</h2>

		<form method="get" action="{{ route('site.users.account.request') }}">
			<div id="request_header">
				<p>
					Purdue researchers collaborating with faculty, staff, or departments who have purchased access to cluster
					nodes or research storage through the <a href="{{ url('/services/communityclusters/') }}">Community Cluster Program</a>
					will be able to select one of the Community Clusters or storage resources on the request form.
				</p>

				<p>
					Once the request is submitted, an email notification will be sent to your faculty advisor requesting approval
					of your request. Account request approvals are left to the discretion of the faculty or appropriate Community
					Cluster partners. <strong>Please contact them directly regarding the status of your request.</strong>
				</p>

				<p>
					To request an {{ config('app.name') }} account please complete the following steps.
				</p>
			</div><!-- / #request_header -->

			<div id="searchbox" class="card">
				<div class="card-header">
					<strong>1)</strong> Use the search box below to find the research group, faculty, or staff member you will be using {{ config('app.name') }} resources to conduct research for.
				</div>
				<div class="card-body">

						<div class="form-group">
							<label for="newuser">Search faculty or research group name:</label><br/>
							<div class="input-group">
								<input type="text" name="newuser" id="newuser" class="form-control searchgroups" data-source="{{ route('api.groups.index', ['api_token' => auth()->user()->api_token, 'search' => '']) }}" autocorrect="off" autocapitalize="off" />
								<!-- <div id="user_results" class="searchMain usersearch_results"></div>
								<select name="newuser" id="newuser" class="form-control searchuser"></select> -->
								<span class="input-group-append">
									<span class="input-group-text">
										<span class="fa fa-search" aria-hidden="true" id="add_button_a"></span>
									</span>
								</span>
							</div>
							<!-- <span id="add_errors"></span> -->
						</div>

				</div>
			</div><!-- / #request_header -->

			<div id="selection_info" class="request-selection">
				<div id="person" class="card" style="display: none">
					<div class="card-header">
						<strong>1)</strong> Person Selected:
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col col-sm-12 col-md-8">
								<span id="personname"></span><br/>
								<span id="title"></span>
							</div>
							<div class="col col-sm-12 col-md-4">
								<a href="{{ route('site.users.account.request') }}" class="request-clear btn" title="Choose another person.">
									<span class="fa fa-undo" aria-hidden="true"></span> Change selection
								</a>
							</div>
						</div>
					</div>
					<input type="hidden" id="selected-user" value="" />
				</div>
				<div id="group" class="card" style="display: none">
					<div class="card-header">
						<strong>1)</strong> Group Selected:
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col col-sm-12 col-md-8">
								<span id="groupname">Name here</span><br/>
								<span id="dept"></span>
							</div>
							<div class="col col-sm-12 col-md-4">
								<a href="{{ route('site.users.account.request') }}" class="request-clear btn" title="Choose another group.">
									<span class="fa fa-undo" aria-hidden="true"></span> Change selection
								</a>
							</div>
						</div>
					</div>
					<input type="hidden" id="selected-group" value="" />
				</div>
			</div><!-- / #selection_info -->

			<div id="resources" class="card request-selection" style="display: none">
				<div class="card-header">
					<strong>2)</strong> Select which resources to request an account on:
				</div>
				<div class="card-body">
					<div id="resourcelist">
					</div>
				</div>
			</div><!-- / #resources -->

			<div id="queues" class="card request-selection" style="display: none">
				<div class="card-header">
					<strong>2)</strong> Select which queues and resources to request access to:
				</div>
				<div class="card-body">
					<div id="queuelist">
					</div>
				</div>
			</div><!-- / #queues -->

			<div id="comments" class="card request-selection" style="display: none">
				<div class="card-header">
					<strong>3)</strong> Enter comments to send with your request <span class="badge badge-secondary">optional</span>:
				</div>
				<div class="card-body">
					<textarea rows="5" cols="60" id="commenttext" class="form-control"></textarea>
				</div>
			</div><!-- / #comments -->

			<div id="controls" class="card request-selection" style="display: none">
				<div class="card-header">
					<strong>4)</strong> Review your request before submitting.
				</div>
				<div class="card-body">
					<p>An email notification will be sent to the managers of the research group or faculty member for approval.</p>

					<p class="alert alert-warning">
						NOTE: Your request will need to be approved by the research group or faculty you have entered.
						Please direct any questions regarding approval status to those people.
					</p>

					<p>
						<input type="button" value="Submit Request" class="request-submit btn btn-primary" />
						<input type="button" value="Cancel" class="request-clear btn btn-default" />
					</p>
				</div>
			</div><!-- / #controls -->

			<div id="confirmation">
				<div id="cluster_confirmation" style="display: none">
					<p>
						Your request has been submitted. An email notification has been sent to the managers of this group
						to approve your request. You will be notified once your request is approved. You may view the status
						of your request from the "My Accounts" tab. <strong>Your request will need to be approved by the
						research group or faculty you have entered. Please direct any questions regarding approval status to
						those people.</strong> Below is a summary of your request.
					</p>
				</div>

				<div id="free_confirmation" style="display: none">
					<p>
						Your request has been submitted. An email notification has been sent to the faculty or staff member or
						managers of this group to approve your request. You will be notified once your request is approved. You
						may view the status of your request from the "My Accounts" tab. <strong>Your request will need to be
						approved by the research group or faculty you have entered. Please direct any questions regarding
						approval status to those people.</strong> Below is a summary of your request.
					</p>
				</div>

				<div id="person_confirmation" class="card" style="display: none">
					<div class="card-header">Faculty/staff member selected:</div>
					<div class="card-body">
						<span id="personname_confirmation"></span>
					</div>
				</div>

				<div id="group_confirmation" class="card" style="display: none">
					<div class="card-header">Research group selected:</div>
					<div class="card-body">
						<span id="groupname_confirmation"></span>
					</div>
				</div>

				<div id="resources_confirmation" class="card" style="display: none">
					<div class="card-header">Requested resources:</div>
					<div class="card-body">
						<div id="resourcelist_confirmation">
						</div>
					</div>
				</div>

				<div id="queues_confirmation" class="card" style="display: none">
					<div class="card-header">Requested queues:</div>
					<div class="card-body">
						<div id="queuelist_confirmation">
						</div>
					</div>
				</div>

				<div id="comment_confirmation" class="card" style="display: none">
					<div class="card-header">Comments:</div>
					<div class="card-body">
						<div id="commenttext_confirmation">
						</div>
					</div>
				</div>
			</div><!-- / #confirmation -->

			<div id="errors"></div>

			@csrf
		</form>
	</div>
</div>
</div>
@stop