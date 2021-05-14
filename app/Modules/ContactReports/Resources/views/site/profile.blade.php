<div class="contentInner">
	<h2>{{ trans('contactreports::contactreports.contact reports') }}</h2>
	<div id="reports">
		@if (!count($reports))
			<p class="alert alert-info">No Contact Reports found for {{ $user->name }}</p>
		@else
			@foreach ($reports as $row)
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
							<li>
								{{ $comment->comment }}
							</li>
						@endforeach
					</ul>
				</article>
			@endforeach
		@endif
	</div>

	{{ $reports->render() }}
</div>