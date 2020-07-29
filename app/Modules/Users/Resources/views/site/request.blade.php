@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('vendor/select2/css/select2.css') }}" />
@endpush

@push('scripts')
<script src="{{ asset('vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/vendor/select2/js/select2.min.js')) }}"></script>
<script src="{{ asset('modules/users/js/request.js?v=' . filemtime(public_path() . '/modules/users/js/request.js')) }}"></script>
<script>
$(document).ready(function() {
	/*var results = [];
	var autocompleteName = function(url) {
		return function(request, response) {
			return $.getJSON(url.replace('%s', encodeURIComponent(request.term)), function (data) {
				response($.map(data.data, function (el) {
					results[el.id] = el;
					return {
						label: el.name,
						name: el.name,
						id: el.id//,
						//usernames: el.usernames,
						//priorusernames: el.priorusernames
					};
				}));
			});
		};
	};

	$(".searchgroups").autocomplete({
		source: autocompleteName("<?php echo url('/'); ?>/api/groups/?api_token=<?php echo auth()->user()->api_token; ?>&searchuser=%s"),
		dataName: 'data',
		height: 150,
		delay: 100,
		minLength: 2,
		filter: /^[a-z0-9\-_ .,@+]+$/i,
		select: function(event, ui) {
			console.log( "Selected: " + ui.item.value + " aka " + ui.item.id );

			var data = results[ui.item.id];
			console.log(data);

				document.getElementById("person").style.display = "none";
				document.getElementById("group").style.display = "block";
				document.getElementById("groupname").innerHTML = data['name'];

				var names = [];
				for (x=0;x<data['department'].length;x++) {
					names.push(data['department'][x]['name']);
				}
				document.getElementById("dept").innerHTML = names.join(', ');

			PrintAccountResources(data);
		}
	});*/



	/*$('.searchuser').select2({
		ajax: {
			url: "<?php echo url('/'); ?>/api/groups/",
			dataType: 'json',
			//maximumSelectionLength: 1,
			//theme: "classic",
			data: function (params) {
				var query = {
					searchuser: params.term,
					//order: 'name',
					//order_dir: 'asc',
					api_token: "<?php echo auth()->user()->api_token; ?>"
				}

				return query;
			},
			processResults: function (data) {
				for (var i = 0; i < data.data.length; i++) {
					data.data[i].text = data.data[i].name; // + ' (' + data.data[i].username + ')';
				}

				return {
					results: data.data
				};
			}
		}
	});
	$('.searchuser')
		.on('select2:select', function (e) {
			var data = e.params.data;
			console.log(data.id);
			//window.location = "<?php echo request()->url(); ?>?id=" + data.id;
		})
		.on('select2:unselect', function (e) {
			var data = e.params.data;
			console.log(data);
			//window.location = "<?php echo request()->url(); ?>";
		});*/
});
</script>
@endpush

@section('content')

<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	<div class="qlinks">
		<ul class="dropdown-menu">
			<li class="active"><a href="{{ route('site.users.account') }}">{{ trans('users::users.my accounts') }}</a></li>
			<li><a href="{{ route('site.users.account.groups') }}">{{ trans('users::users.my groups') }}</a></li>
			<li><a href="{{ route('site.users.account.quotas') }}">{{ trans('users::users.my quotas') }}</a></li>
			<li><a href="{{ route('site.orders.index') }}">{{ trans('users::users.my orders') }}</a></li>
		</ul>
	</div>
</div>

<div class="col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<div class="contentInner">
		<h2>{{ trans('users::users.request access') }}</h2>

		<form method="get" action="{{ route('site.users.account.request') }}">
			<div id="request_header">
				<p>Purdue researchers collaborating with faculty, staff, or departments who have purchased access to cluster nodes or research storage through the <a href="/services/communityclusters/">Community Cluster Program</a> will be able to select one of the Community Clusters or storage resources on the request form.</p> 

				<p>Once the request is submitted, an email notification will be sent to your faculty advisor requesting approval of your request. Account request approvals are left to the discretion of the faculty or appropriate Community Cluster partners. <strong>Please contact them directly regarding the status of your request.</strong></p>

				<p>
					To request an ITaP Research Computing account please complete the following steps.
				</p>
			</div><!-- / #request_header -->

			<div id="searchbox" class="panel panel-default">
				<div class="panel-heading">
					<strong>1)</strong> Use the search box below to find the research group, faculty, or staff member you will be using ITaP Research Computing resources to conduct research for.
				</div>
				<div class="panel-body">
					
						<div class="form-group">
							<label for="newuser">Search faculty or research group name:</label><br/>
							<div class="input-group">
								<input type="text" name="newuser" id="newuser" class="form-control searchgroups" data-source="{{ route('api.groups.index', ['api_token' => auth()->user()->api_token, 'searchuser' => '']) }}" autocorrect="off" autocapitalize="off" />
								<!-- <div id="user_results" class="searchMain usersearch_results"></div>
								<select name="newuser" id="newuser" class="form-control searchuser"></select> -->
								<div class="input-group-addon">
									<span class="input-group-text">
										<i class="fa fa-search" aria-hidden="true" id="add_button_a"></i>
										<!-- <img src="/include/images/loading.gif" width="14" id="search_loading" alt="Loading..." class="icon" /> -->
									</span>
								</div>
							</div>
							<!-- <span id="add_errors"></span> -->
						</div>
					
				</div>
			</div><!-- / #request_header -->

			<div id="selection_info" class="request-selection">
				<div id="person" class="panel panel-default" style="display: none">
					<div class="panel-heading">
						<strong>1)</strong> Person Selected:
					</div>
					<div class="panel-body">
						<div class="row">
							<div class="col col-sm-12 col-md-8">
								<span id="personname"></span><br/>
								<span id="title"></span>
							</div>
							<div class="col col-sm-12 col-md-4">
								<a href="{{ route('site.users.account.request') }}" class="request-clear btn btn-default" title="Choose another person.">
									<i class="fa fa-undo" aria-hidden="true"></i> Change selection
								</a>
							</div>
						</div>
					</div>
					<input type="hidden" id="selected-user" value="" />
				</div>
				<div id="group" class="panel panel-default" style="display: none">
					<div class="panel-heading">
						<strong>1)</strong> Group Selected:
					</div>
					<div class="panel-body">
						<div class="row">
							<div class="col col-sm-12 col-md-8">
								<span id="groupname">Name here</span><br/>
								<span id="dept"></span>
							</div>
							<div class="col col-sm-12 col-md-4">
								<a href="{{ route('site.users.account.request') }}" class="request-clear btn btn-default" title="Choose another group.">
									<i class="fa fa-undo" aria-hidden="true"></i> Change selection
								</a>
							</div>
						</div>
					</div>
					<input type="hidden" id="selected-group" value="" />
				</div>
			</div><!-- / #selection_info -->

			<div id="resources" class="panel panel-default request-selection" style="display: none">
				<div class="panel-heading">
					<strong>2)</strong> Select which resources to request an account on:
				</div>
				<div class="panel-body">
					<div id="resourcelist">
					</div>
				</div>
			</div><!-- / #resources -->

			<div id="queues" class="panel panel-default request-selection" style="display: none">
				<div class="panel-heading">
					<strong>2)</strong> Select which queues and resources to request access to:
				</div>
				<div class="panel-body">
					<div id="queuelist">
					</div>
				</div>
			</div><!-- / #queues -->

			<div id="comments" class="panel panel-default request-selection" style="display: none">
				<div class="panel-heading">
					<strong>3)</strong> Enter comments to send with your request <span class="badge">optional</span>:
				</div>
				<div class="panel-body">
					<textarea rows="5" cols="60" id="commenttext" class="form-control"></textarea>
				</div>
			</div><!-- / #comments -->

			<div id="controls" class="panel panel-default request-selection" style="display: none">
				<div class="panel-heading">
					<strong>4)</strong> Review your request before submitting.
				</div>
				<div class="panel-body">
					<p>An email notification will be sent to the managers of the research group or faculty member for approval.</p>

					<p class="alert alert-warning">NOTE: Your request will need to be approved by the research group or faculty you have entered. Please direct any questions regarding approval status to those people.</p>

					<p>
						<input type="button" value="Submit Request" class="request-submit btn btn-primary" />
						<input type="button" value="Cancel" class="request-clear btn btn-default" />
					</p>
				</div>
			</div><!-- / #controls -->

			<div id="confirmation">
				<div id="cluster_confirmation" style="display: none">
					<p>Your request has been submitted. An email notification has been sent to the managers of this group to approve your request. You will be notified once your request is approved. You may view the status of your request from the "My Accounts" tab. <strong>Your request will need to be approved by the research group or faculty you have entered. Please direct any questions regarding approval status to those people.</strong> Below is a summary of your request.</p>
				</div>

				<div id="free_confirmation" style="display: none">
					<p>Your request has been submitted. An email notification has been sent to the faculty or staff member or managers of this group to approve your request. You will be notified once your request is approved. You may view the status of your request from the "My Accounts" tab. <strong>Your request will need to be approved by the research group or faculty you have entered. Please direct any questions regarding approval status to those people.</strong> Below is a summary of your request.</p>
				</div>

				<div id="person_confirmation" class="panel panel-default" style="display: none">
					<div class="panel-heading">Faculty/staff member selected:</div>
					<div class="panel-body">
						<span id="personname_confirmation"></span>
					</div>
				</div>

				<div id="group_confirmation" class="panel panel-default" style="display: none">
					<div class="panel-heading">Research group selected:</div>
					<div class="panel-body">
						<span id="groupname_confirmation"></span>
					</div>
				</div>

				<div id="resources_confirmation" class="panel panel-default" style="display: none">
					<div class="panel-heading">Requested resources:</div>
					<div class="panel-body">
						<div id="resourcelist_confirmation">
						</div>
					</div>
				</div>

				<div id="queues_confirmation" class="panel panel-default" style="display: none">
					<div class="panel-heading">Requested queues:</div>
					<div class="panel-body">
						<div id="queuelist_confirmation">
						</div>
					</div>
				</div>

				<div id="comment_confirmation" class="panel panel-default" style="display: none">
					<div class="panel-heading">Comments:</div>
					<div class="panel-body">
						<div id="commenttext_confirmation">
						</div>
					</div>
				</div>
			</div><!-- / #confirmation -->

			<div>
				<p>If you encounter any issues with this request form or have any questions contact <a href="mailto:rcac-help@purdue.edu">rcac-help@purdue.edu</a>.</p>
			</div>

		</form>
	</div>
</div>

@stop