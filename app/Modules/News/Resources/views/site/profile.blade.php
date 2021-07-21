<div class="contentInner">
	<h2>{{ $type->name }}</h2>

	<div id="reports">
		@if (!count($rows))
			<div class="card card-help">
				<div class="card-body">
					<h3 class="card-title">What is this page?</h3>
					<p>If {{ $user->name }} has registered for any events, you'll find them listed here.</p>
				</div>
			</div>
		@else
			@foreach ($rows as $row)
				<article id="{{ $row->id }}" class="crm-item newEntries">
					<div class="card panel panel-default">
						<div class="card-header panel-heading news-admin">
							<span class="newsid"><a href="{{ route('site.news.show', ['id' => $row->id]) }}">#{{ $row->id }}</a></span>
							@if (auth()->user()->can('manage news'))
								<span class="newspublication">
									@if ($row->published)
										<span class="badge badge-published">Published</span>
									@else
										<span class="badge badge-unpublished">Unpublished</span>
									@endif
								</span>
							@endif
						</div>
						<div class="card-header panel-heading">
							<h3 class="card-title panel-title crmcontactdate">{{ $row->headline }}</h3>
							<ul class="card-meta panel-meta news-meta">
								<li class="news-date"><span class="newspostdate">Posted on {{ $row->datetimecreated->format('M d, Y') }}</span></li>
								<li class="news-author"><span class="newsposter">Posted by {{ $row->creator->name }}</span></li>
								<li class="news-type"><span class="newstype">{{ $type->name }}</span></li>
								@if ($row->location)
								<li class="news-location">{{ $row->location }}</li>
								@endif
								@if ($row->url)
								<li class="news-url"><a href="{{ $row->url }}">{{ $row->url }}</a></li>
								@endif
								<?php
								if (count($row->resources) > 0)
								{
									$resourceArray = array();
									foreach ($row->resources as $resource)
									{
										$resourceArray[] = $resource->resource->name;
									}
									?>
									<li class="news-tags"><span class="newspostresources">{{ implode(', ', $resourceArray) }}</span></li>
									<?php
								}

								$users = $row->associations()->where('assoctype', '=', 'user')->get();
								?>
							@if (auth()->user()->can('manage news'))
								@if ($users->count())
									<?php
									$names = array();
									foreach ($users as $usr):
										$u = App\Modules\Users\Models\User::find($usr->associd);
										if (!$u)
										{
											continue;
										}
										$names[] = '<a href="' . route('site.users.account', ['u' => $u->id]) . '">' . e($u->name) . '</a>';
									endforeach;
									?>
									<li class="news-users">
										<span id="newspostusers-{{ $row->id }}" class="newspostusers">
											({{ $users->count() }}) - {!! implode(', ', $names) !!}
										</span>
									</li>
								@endif
							@endif
							</ul>
						</div>
						<div class="card-body panel-body">
							<div class="newsposttext">
								<span id="{{ $row->id }}_text">{!! $row->formattedBody !!}</span>
							</div>
						</div>
					</div>
					<ul id="{{ $row->id }}_updates" class="news-updates">
						@foreach ($row->updates()->orderBy('datetimecreated', 'asc')->get() as $update)
							<li>
								<div class="card panel panel-default">
									<div class="card-body panel-body">
										{!! $update->formattedBody !!}
									</div>
									<div class="card-footer panel-footer">
										<div class="crmcommentpostedby">Posted by {{ $update->creator ? $update->creator->name : trans('global.unknown') }} on {{ $update->formattedDatetimecreated($update->datetimecreated->toDateTimeString()) }}</div>
									</div>
								</div>
							</li>
						@endforeach
					</ul>
				</article>
			@endforeach
		@endif
	</div>

	{{ $rows->render() }}
</div>