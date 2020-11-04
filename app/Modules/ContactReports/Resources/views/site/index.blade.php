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
	<a href="#">Something here</a>
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<h2>{{ trans('contactreports::contactreports.contact reports') }}</h2>

<div id="everything">
	<ul class="nav nav-tabs crm-tabs">
		<li data-toggle="tab"><a id="TAB_search" class="tab activeTab" href="#search">Search</a></li>
		<li data-toggle="tab"><a id="TAB_add" class="tab" href="#add">Add New</a></li>
		<li data-toggle="tab"><a id="TAB_follow" class="tab" href="#follow">Follow</a></li>
	</ul>
	<div class="tabMain" id="tabMain">
		<div id="DIV_crm">

			<form method="get" action="{{ route('site.contactreports.index') }}">
				<fieldset>
					<legend><span id="SPAN_header" data-search="Search Reports" data-add="Add New Reports" data-edit="Edit Reports">Search Reports</span></legend>

					<div class="form-group row tab-search tab-add tab-edit" id="TR_date">
						<label for="datestartshort" class="col-sm-2 col-form-label">Date from</label>
						<div class="col-sm-4">
							<div class="input-group">
								<span class="input-group-addon"><span class="input-group-text fa fa-calendar" aria-hidden="true"></span></span>
								<input id="datestartshort" type="text" class="date-pick form-control" name="start" placeholder="YYYY-MM-DD" value="{{ $filters['start'] }}" />
							</div>
						</div>

						<label for="datestopshort" class="col-sm-2 col-form-label align-right tab-search">Date to</label>
						<div class="col-sm-4 tab-search">
							<div class="input-group" id="enddate">
								<span class="input-group-addon"><span class="input-group-text fa fa-calendar" aria-hidden="true"></span></span>
								<input id="datestopshort" type="text" class="date-pick form-control" name="stop" placeholder="YYYY-MM-DD" value="{{ $filters['stop'] }}">
							</div>
						</div>
					</div>

					<div class="form-group row tab-search" id="TR_keywords">
						<label for="keywords" class="col-sm-2 col-form-label">Keywords</label>
						<div class="col-sm-10">
							<input type="text" name="keyword" id="keywords" size="45" class="form-control" value="{{ $filters['search'] }}" />
						</div>
					</div>

					<div class="form-group row tab-search tab-add tab-edit tab-follow" id="TR_group">
						<label for="group" class="col-sm-2 col-form-label">Group</label>
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
							<input name="group" id="group" size="45" class="form-control" value="{{ implode(',', $grps) }}" data-uri="{{ route('api.groups.index') }}/%s" />
						</div>
					</div>

					<div class="form-group row tab-search tab-add tab-edit tab-follow" id="TR_people">
						<label for="people" class="col-sm-2 col-form-label">People</label>
						<div class="col-sm-10">
							<?php
							$usrs = array();
							if ($users = $filters['people'])
							{
								foreach (explode(',', $users) as $u)
								{
									if (trim($u))
									{
										/*if (!strstr($u, '/'))
										{
											if (is_numeric($u))
											{
												$u = ROOT_URI . 'user/' . $u;
												$usr = $ws->get($u);
											}
											else
											{
												$usr = App\Modules\Users\Models\User::find($u);
											}
										}
										else
										{*/
											$usr = App\Modules\Users\Models\User::find($u);
										//}
										$usrs[] = $usr->name . ':' . $u;
									}
								}
							}
							?>
							<input name="people" id="people" size="45" class="form-control" value="{{ implode(',', $usrs) }}" data-uri="{{ route('api.users.index') }}/%s" />
						</div>
					</div>

					<div class="form-group row tab-search tab-add tab-edit" id="TR_resource">
						<label for="newsresource" class="col-sm-2 col-form-label">Resource</label>
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
							<input name="resource" id="crmresource" size="45" class="form-control" value="{{ implode(',', $resources) }}" data-uri="{{ route('api.resources.index') }}/%s" />
						</div>
					</div>

					<div class="form-group row tab-search" id="TR_id">
						<label for="id" class="col-sm-2 col-form-label">CR#</label>
						<div class="col-sm-10">
							<input name="id" type="text" id="id" size="45" class="form-control" value="{{ $filters['id'] }}" />
						</div>
					</div>

					<div class="form-group row tab-add tab-edit hide" id="TR_notes">
						<label for="NotesText" class="col-sm-2 col-form-label">Notes</label>
						<div class="col-sm-10">
							<textarea id="NotesText" class="form-control" rows="10" cols="80"></textarea>
						</div>
					</div>

					<div class="form-group row tab-search" id="TR_search">
						<div class="col-sm-2">
						</div>
						<div class="col-sm-10 offset-sm-10">
							<input type="submit" class="btn btn-primary" value="Search" id="INPUT_search" />
							<input type="reset" class="btn btn-default btn-clear" value="Clear" id="INPUT_clearsearch" />
						</div>
					</div>

					<div class="form-group row tab-add tab-edit hide" id="TR_create">
						<div class="col-sm-2">
						</div>
						<div class="col-sm-10 offset-sm-10">
							<input id="INPUT_add" type="submit" class="btn btn-primary" value="Add Report" disabled="true" />
							<input id="INPUT_clear" type="reset" class="btn btn-default btn-clear" value="Clear" />
						</div>
					</div>

					<input id="myuserid" type="hidden" value="{{ auth()->user()->id }}" />

					<span id="TAB_search_action"></span>
					<span id="TAB_add_action"></span>
					<span id="crm_action"></span>
				</fieldset>
			</form>

		</div>
	</div>

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

	<div id="reports" data-query="{{ $string }}">
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
	</div>

	<script type="application/json" id="crm-search-data">
		{
			"followerofgroups": <?php echo json_encode(auth()->user()->followerofgroups); ?>,
			"followerofusers": <?php echo json_encode(auth()->user()->followerofusers); ?>,
			"groups": <?php echo request()->has('groups') ? json_encode(explode(',', request()->input('groups'))) : '[]'; ?>,
			"people": <?php echo request()->has('people') ? json_encode(explode(',', request()->input('people'))) : '[]'; ?>
		}
	</script>
	<!-- <table class="table table-hover adminlist">
		<thead>
			<tr>
				<th>
					{!! Html::grid('checkall') !!}
				</th>
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('contactreports::contactreports.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('contactreports::contactreports.report'), 'report', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('contactreports::contactreports.group'), 'groupid', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('contactreports::contactreports.contacted'), 'datetimecontact', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-2">
					{{ trans('contactreports::contactreports.comments') }}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				<td>
					@if (auth()->user()->can('edit contactreports'))
						<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
					@endif
				</td>
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					@if (auth()->user()->can('edit contactreports'))
						<a href="{{ route('admin.contactreports.edit', ['id' => $row->id]) }}">
							{{ Illuminate\Support\Str::limit($row->report, 70) }}
						</a>
					@else
						<span>
							{{ Illuminate\Support\Str::limit($row->report, 70) }}
						</span>
					@endif
				</td>
				<td class="priority-4">
					{{ $row->group ? $row->group->name : trans('global.none') }}
				</td>
				<td class="priority-4">
					<span class="datetime">
						@if ($row->getOriginal('datetimecontact') && $row->getOriginal('datetimecontact') != '0000-00-00 00:00:00')
							<time datetime="{{ $row->datetimecontact }}">
								@if ($row->datetimecontact->format('Y-m-dTh:i:s') > Carbon\Carbon::now()->toDateTimeString())
									{{ $row->datetimecontact->diffForHumans() }}
								@else
									{{ $row->datetimecontact->format('Y-m-d') }}
								@endif
							</time>
						@else
							<span class="never">{{ trans('global.unknown') }}</span>
						@endif
					</span>
				</td>
				<td class="priority-4">
					<a href="{{ route('admin.contactreports.comments', ['report' => $row->id]) }}">
						{{ $row->comments_count }}
					</a>
				</td>
			</tr>
		@endforeach
		</tbody>
	</table> -->

		{{ $rows->render() }}
	</div>
</div>

@stop