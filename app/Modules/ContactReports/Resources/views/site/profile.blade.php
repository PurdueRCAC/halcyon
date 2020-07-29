<div class="contentInner">
	<h2>{{ trans('contactreports::contactreports.contact reports') }}</h2>
	<div id="reports">
		@if (!count($reports))
			<p class="alert alert-info">No Contact Reports found for {{ $user->name }}</p>
		@else
			@foreach ($reports as $row)
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