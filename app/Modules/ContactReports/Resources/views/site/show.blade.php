@extends('layouts.master')

@php
app('pathway')
	->append(
		trans('contactreports::contactreports.contact reports'),
		route('site.contactreports.index')
	)
	->append(
		'#' . $row->id,
		route('site.contactreports.show', ['id' => $row->id])
	);
@endphp

@section('title'){{ trans('contactreports::contactreports.contact reports') }}: #{{ $row->id }}@stop

@section('content')
<div class="sidenav col-lg-3 col-md-3 col-sm-12 col-xs-12">
	<h2>Quick Filters</h2>
	<ul class="nav flex-column">
		<li class="nav-item">
			<a class="nav-link" href="{{ route('site.contactreports.index') }}?people={{ auth()->user()->id }}">
				{{ trans('contactreports::contactreports.my reports') }}
			</a>
		</li>
		<li class="nav-item">
			<?php
			$start = Carbon\Carbon::now()->modify('-1 week')->format('Y-m-d');
			?>
			<a class="nav-link" href="{{ route('site.contactreports.index') }}?start={{ $start }}">
				{{ trans('contactreports::contactreports.past week') }}
			</a>
		</li>
		<li class="nav-item">
			<?php
			$start = Carbon\Carbon::now()->modify('-1 month')->format('Y-m-d');
			?>
			<a class="nav-link" href="{{ route('site.contactreports.index') }}?start={{ $start }}">
				{{ trans('contactreports::contactreports.past month') }}
			</a>
		</li>
	</ul>
</div>

<div class="contentInner col-lg-9 col-md-9 col-sm-12 col-xs-12">
	<h2>{{ trans('contactreports::contactreports.contact reports') }}: #{{ $row->id }}</h2>

	<div id="reports">
		<article id="{{ $row->id }}" class="crm-item newEntries">
			<div class="card panel panel-default">
				<div class="card-header panel-heading news-admin">
					<span class="crmid"><a href="{{ route('site.contactreports.show', ['id' => $row->id]) }}">#{{ $row->id }}</a></span>
				</div>
				<div class="card-header panel-heading">
					<h3 class="card-title panel-title crmcontactdate">{{ $row->datetimecontact->format('M d, Y') }}</h3>
					<ul class="card-meta panel-meta news-meta">
						<li class="news-date"><span class="crmpostdate">Posted on {{ $row->datetimecreated->format('M d, Y') }}</span></li>
						@if ($row->creator)
							<li class="news-author"><span class="crmposter">Posted by {{ $row->creator->name }}</span></li>
						@endif
						@if ($row->group)
							<li class="news-group">{{ $row->group->name }}</li>
						@endif
						@if (count($row->users))
							<?php
							$users = array();
							foreach ($row->users as $u)
							{
								$users[] = '<a href="' . route('site.users.account', ['u' => $u->userid]). '">' . $u->user ? $u->user->name : trans('global.unknown') . ' (#' . $u->userid . ')' . '</a>';
							}
							?>
							<li class="news-users"><span class="crmusers">{!! implode(', ', $users) !!}</span></li>
						@endif
						@if (count($row->resources))
							<?php
							$resources = array();
							foreach ($row->resources as $r)
							{
								$resources[] = $r->resource ? e($r->resource->name) : trans('global.unknown') . ' (#' . $r->resourceid. ')';
							}
							?>
							<li class="news-tags"><span class="crmresources">{!! implode(', ', $resources) !!}</span></li>
						@endif
					</ul>
				</div>
				<div class="card-body panel-body">
					<div class="newsposttext">
						<span id="{{ $row->id }}_text">{!! $row->formattedReport !!}</span>
					</div>
				</div>
			</div>
			<ul id="{{ $row->id }}_comments" class="crm-comments">
				@foreach ($row->comments()->orderBy('datetimecreated', 'asc')->get() as $comment)
					<li id="comment_{{ $comment->id }}" data-api="{{ route('api.contactreports.comments.read', ['id' => $comment->id]) }}">
						<div class="card mb-3 panel panel-default">
							<div class="card-header panel-heading cdm-admin">
								<span class="crmid">#{{ $comment->id }}</span>
							</div>
							<div class="card-body panel-body crmcomment crmcommenttext">
								{{ $comment->comment }}
							</div>
							<div class="card-footer panel-footer">
								<div class="crmcommentpostedby">Posted by {{ $comment->creator ? $comment->creator->name : trans('global.unknown') }} on {!! $comment->formattedDate !!}</div>
							</div>
						</div>
					</li>
				@endforeach
			</ul>
		</article>
	</div>
</div>
@stop