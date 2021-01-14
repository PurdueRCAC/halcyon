@extends('layouts.master')

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.css?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/tagsinput/jquery.tagsinput.js?v=' . filemtime(public_path() . '/modules/core/vendor/tagsinput/jquery.tagsinput.js')) }}"></script>
<script src="{{ asset('modules/contactreports/js/site.js') }}"></script>
@endpush

@section('content')
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	<h2>Quick Filters</h2>
	<ul class="dropdown-menu">
		<li>
			<a href="{{ route('site.contactreports.index') }}?people={{ auth()->user()->id }}">
				{{ trans('contactreports::contactreports.my reports') }}
			</a>
		</li>
		<li>
			<?php
			$start = Carbon\Carbon::now()->modify('-1 week')->format('Y-m-d');
			?>
			<a href="{{ route('site.contactreports.index') }}?start={{ $start }}">
				{{ trans('contactreports::contactreports.past week') }}
			</a>
		</li>
		<li>
			<?php
			$start = Carbon\Carbon::now()->modify('-1 month')->format('Y-m-d');
			?>
			<a href="{{ route('site.contactreports.index') }}?start={{ $start }}">
				{{ trans('contactreports::contactreports.past month') }}
			</a>
		</li>
	</ul>
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<h2>{{ trans('contactreports::contactreports.contact reports') }}</h2>

	<div id="everything">
		<ul class="nav nav-tabs crm-tabs">
			<li class="nav-item"><a id="TAB_search" class="nav-link active tab activeTab" href="#search">{{ trans('contactreports::contactreports.search') }}</a></li>
			<li class="nav-item"><a id="TAB_add" class="nav-link tab" href="#add">{{ trans('contactreports::contactreports.add new') }}</a></li>
			<li class="nav-item"><a id="TAB_follow" class="nav-link tab" href="#follow">{{ trans('contactreports::contactreports.follow') }}</a></li>
		</ul>
		<div class="tabMain" id="tabMain">
			<div id="DIV_crm">

				<form method="get" action="{{ route('site.contactreports.index') }}">
					<fieldset>
						<legend><span id="SPAN_header" data-search="Search Reports" data-add="Add New Reports" data-edit="Edit Reports">Search Reports</span></legend>

						<div class="form-group row tab-search tab-add tab-edit" id="TR_date">
							<label for="datestartshort" class="col-sm-2 col-form-label">{{ trans('contactreports::contactreports.date from') }}</label>
							<div class="col-sm-4">
								<div class="input-group">
									<span class="input-group-addon"><span class="input-group-text fa fa-calendar" aria-hidden="true"></span></span>
									<input id="datestartshort" type="text" class="date-pick form-control" name="start" placeholder="YYYY-MM-DD" value="{{ $filters['start'] }}" />
								</div>
							</div>

							<label for="datestopshort" class="col-sm-2 col-form-label align-right tab-search">{{ trans('contactreports::contactreports.date to') }}</label>
							<div class="col-sm-4 tab-search">
								<div class="input-group" id="enddate">
									<span class="input-group-addon"><span class="input-group-text fa fa-calendar" aria-hidden="true"></span></span>
									<input id="datestopshort" type="text" class="date-pick form-control" name="stop" placeholder="YYYY-MM-DD" value="{{ $filters['stop'] }}">
								</div>
							</div>
						</div>

						<div class="form-group row tab-search" id="TR_keywords">
							<label for="keywords" class="col-sm-2 col-form-label">{{ trans('contactreports::contactreports.keywords') }}</label>
							<div class="col-sm-10">
								<input type="text" name="keyword" id="keywords" size="45" class="form-control" value="{{ $filters['search'] }}" />
							</div>
						</div>

						<div class="form-group row tab-search tab-add tab-edit tab-follow" id="TR_group">
							<label for="group" class="col-sm-2 col-form-label">{{ trans('contactreports::contactreports.group') }}</label>
							<div class="col-sm-10">
								<?php
								$grps = array();
								if ($groups = $filters['group'])
								{
									foreach (explode(',', $groups) as $g)
									{
										if (trim($g))
										{
											/*if (!strstr($g, '/'))
											{
												$g = ROOT_URI . 'group/' . $g;
											}*/
											$grp = App\Modules\Groups\Models\Group::find($g);
											$grps[] = $grp->name . ':' . $g . '';
										}
									}
								}
								?>
								<input name="group" id="group" size="45" class="form-control" value="{{ implode(',', $grps) }}" data-uri="{{ route('api.groups.index') }}?search=%s" data-api="{{ route('api.groups.index') }}" />
							</div>
						</div>

						<div class="form-group row tab-search tab-add tab-edit tab-follow" id="TR_people">
							<label for="people" class="col-sm-2 col-form-label">{{ trans('contactreports::contactreports.users') }}</label>
							<div class="col-sm-10">
								<?php
								$usrs = array();
								if ($users = $filters['people'])
								{
									foreach (explode(',', $users) as $u)
									{
										if (trim($u))
										{
											$usr = App\Modules\Users\Models\User::find($u);
											$usrs[] = $usr->name . ':' . $u;
										}
									}
								}
								?>
								<input name="people" id="people" size="45" class="form-control" value="{{ implode(',', $usrs) }}" data-uri="{{ route('api.users.index') }}?search=%s" data-api="{{ route('api.users.index') }}" />
							</div>
						</div>

						<div class="form-group row tab-search tab-add tab-edit" id="TR_resource">
							<label for="newsresource" class="col-sm-2 col-form-label">{{ trans('contactreports::contactreports.resources') }}</label>
							<div class="col-sm-10">
								<?php
								$resources = array();
								if ($rs = $filters['resource'])
								{
									foreach (explode(',', $rs) as $r)
									{
										if (trim($r))
										{
											$resource = App\Modules\Resources\Entities\Asset::find($r);
											$resources[] = $resource->name . ':' . $r;
										}
									}
								}
								?>
								<input name="resource" id="crmresource" size="45" class="form-control" value="{{ implode(',', $resources) }}" data-uri="{{ route('api.resources.index') }}?search=%s" data-api="{{ route('api.resources.index') }}" />
							</div>
						</div>

						<div class="form-group row tab-search" id="TR_id">
							<label for="id" class="col-sm-2 col-form-label">CR#</label>
							<div class="col-sm-10">
								<input name="id" type="text" id="id" size="45" class="form-control" value="{{ $filters['id'] }}" />
							</div>
						</div>

						<div class="form-group row tab-add tab-edit" id="TR_notes">
							<label for="NotesText" class="col-sm-2 col-form-label">{{ trans('contactreports::contactreports.notes') }}</label>
							<div class="col-sm-10">
								<textarea id="NotesText" class="form-control" rows="10" cols="80"></textarea>
							</div>
						</div>

						<div class="form-group row tab-search" id="TR_search">
							<div class="col-sm-2">
							</div>
							<div class="col-sm-10">
								<input type="submit" class="btn btn-primary" value="{{ trans('contactreports::contactreports.search') }}" id="INPUT_search" />
								<input type="reset" class="btn btn-link btn-clear" value="{{ trans('contactreports::contactreports.clear') }}" id="INPUT_clearsearch" />
							</div>
						</div>

						<div class="form-group row tab-add tab-edit" id="TR_create">
							<div class="col-sm-2">
							</div>
							<div class="col-sm-10">
								<input id="INPUT_add" type="submit" class="btn btn-primary" value="{{ trans('contactreports::contactreports.add report') }}" disabled="true" />
								<input id="INPUT_clear" type="reset" class="btn btn-link btn-clear" value="{{ trans('contactreports::contactreports.clear') }}" />
							</div>
						</div>

						<input id="myuserid" type="hidden" value="{{ auth()->user()->id }}" />

						<span id="TAB_search_action"></span>
						<span id="TAB_add_action"></span>
						<span id="crm_action"></span>
					</fieldset>
				</form>

			</div><!-- / #DIV_crm -->
		</div><!-- / #tabMain -->

		<?php
		$valid_args = array('start', 'stop', 'id', 'group', 'people', 'resource', 'search');

		$string = array();

		foreach ($valid_args as $key)
		{
			if (request()->has($key))
			{
				$string[] = $key . '=' . request()->input($key);
			}
		}

		$string = implode('&', $string);
		?>
		<div id="reports" data-query="{{ $string }}" data-api="{{ route('api.contactreports.index') }}" data-comments="{{ route('api.contactreports.comments') }}">
			<?php
			/*
			@foreach ($rows as $i => $row)
				<article id="{{ $row->id }}" class="crm-item newEntries">
					<div class="panel panel-default">
						<div class="panel-heading news-admin">
							<span class="crmid"><a href="{{ route('site.contactreports.show', ['id' => $row->id]) }}">#{{ $row->id }}</a></span>
						</div>
						<div class="panel-heading">
							<h3 class="panel-title crmcontactdate">{{ $row->datetimecontact->format('M d, Y') }}</h3>
							<ul class="panel-meta news-meta">
								<li class="news-date"><span class="crmpostdate">Posted on {{ $row->datetimecreated->format('M d, Y') }}</span></li>
								<li class="news-author"><span class="crmposter">Posted by {{ $row->creator->name }}</span></li>
							</ul>
						</div>
						<div class="panel-body">
							<div class="newsposttext">
								<span id="{{ $row->id }}_text">{!! $row->formattedReport !!}</span>
								<span><textarea id="{{ $row->id }}_textarea" rows="7" cols="45" class="form-control crmreportedittextbox" style="display: none;"></textarea></span>
							</div>
						</div>
					</div>
					<div class="crmnewcomment panel panel-default">
						<div class="panel-heading">
							<div class="crmcomment crmsubscribe" id="{{ $row->id }}_subscribed">
								<a href="{{ route('site.contactreports.show', ['id' => $row->id, 'subscribe' => 1]) }}" class="btn btn-default btn-sm">Subscribe</a>
							</div>
						</div>
						<div class="panel-body">
							<div id="{{ $row->id }}_newupdate">
								<textarea class="form-control crmcommentbox" placeholder="Write a comment..." id="5037_newcommentbox" rows="1" cols="45"></textarea>
								<a href="/news/manage?update&amp;id=5037" title="Add a new comment."><i class="fa fa-save" aria-hidden="true" id="5037_newcommentboxsave" style="display: none;"></i></a>
							</div>
						</div>
					</div>
					<ul id="{{ $row->id }}_comments" class="crm-comments">
						@foreach ($row->comments()->orderBy('datetimecreated', 'asc')->get() as $comment)
						@endforeach
					</ul>
				</article>
			@endforeach
			*/
			?>
		</div><!-- / #reports -->
		<?php /*
		{{ $rows->render() }}
		*/ ?>

		@if ($report)
		<script type="application/json" id="crm-data">
			{
				"original": {
					"id": "<?php echo $report->id; ?>",
					"api": "<?php echo route('api.contactreports.update', ['id' => $report->id]); ?>",
					"datetimecontact": "<?php echo $report->datetimecontact->format('Y-m-d'); ?>",
					"groupid": "<?php echo $report->groupid; ?>",
					"groupname": "<?php echo $report->group ? $report->group->name : ''; ?>",
					"groupage": "<?php echo $report->groupage; ?>",
					"age": "",
					"users": <?php echo json_encode($report->users); ?>,
					"resources": <?php echo json_encode($report->resources); ?>,
					"note": <?php echo json_encode($report->report); ?>
				},
				"originalusers": [],
				"originalcontactusers": []
			}
		</script>
		@endif

		<script type="application/json" id="crm-search-data">
			{
				"followerofgroups": <?php echo json_encode($followinggroups); ?>,
				"followerofusers": <?php echo json_encode($followingusers); ?>,
				"groups": <?php echo request()->has('groups') ? json_encode(explode(',', request()->input('groups'))) : '[]'; ?>,
				"people": <?php echo request()->has('people') ? json_encode(explode(',', request()->input('people'))) : '[]'; ?>
			}
		</script>
	</div><!-- / #everything -->
</div><!-- / .contentInner -->
@stop