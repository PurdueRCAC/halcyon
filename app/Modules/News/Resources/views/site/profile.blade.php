@push('scripts')
<script src="{{ asset('modules/news/js/site.js?v=' . filemtime(public_path() . '/modules/news/js/site.js')) }}"></script>
@endpush

<div class="contentInner">
	<h2>{{ $type->name }}</h2>

	<div id="reports">
		@if (!count($rows))
			<div class="d-flex justify-content-center">
				<div class="card card-help w-50">
					<div class="card-body">
						<h3 class="card-title mt-0">{{ trans('news::news.user news header') }}</h3>
						<p class="card-text">{{ trans('news::news.user news explanation') }}</p>
					</div>
				</div>
			</div>
		@else
			@foreach ($rows as $row)
				<article id="{{ $row->id }}" class="crm-item newEntries">
					<div class="card panel panel-default">
						@if (auth()->user()->can('manage news') || !$row->ended())
							<div class="card-header panel-heading news-admin">
								@if (auth()->user()->can('manage news'))
									<span class="newsid">#{{ $row->id }}</span>
									<span class="newspublication">
										@if ($row->published)
											<span class="badge badge-published">{{ trans('global.published') }}</span>
										@else
											<span class="badge badge-unpublished">{{ trans('global.unpublished') }}</span>
										@endif
									</span>
								@else
									<div class="text-right">
										<a class="btn-notattend btn btn-danger" href="{{ route('page', ['uri' => 'coffee', 'attend' => 0]) }}" data-id="{{ $row->attending }}">{{ trans('news::news.cancel reservation') }}</a>
									</div>
								@endif
							</div>
						@endif
						<div class="card-header panel-heading">
							<h3 class="card-title panel-title crmcontactdate"><a href="{{ route('site.news.show', ['id' => $row->id]) }}">{{ $row->headline }}</a></h3>
							<ul class="card-meta panel-meta news-meta">
								@if (auth()->user() && auth()->user()->can('manage news'))
									<li class="news-date"><span class="newspostdate">{!! trans('posted on date', ['date' => '<time datetime="' . $row->datetimenews->toDateTimeLocalString() . '">' . $row->datetimecreated->format('M d, Y') . '</time>'] !!}</span></li>
									<li class="news-author"><span class="newsposter">{{ trans('posted by name', ['name' => $row->creator->name]) }}</span></li>
								@endif
								<li class="news-date">{!! $row->formatDate($row->datetimenews, $row->datetimenewsend) !!}
									@if ($row->isToday())
										@if ($row->isNow())
											<span class="badge badge-success">{{ trans('news::news.happening now') }}</span>
										@else
											<span class="badge badge-info">{{ trans('news::news.today') }}</span>
										@endif
									@elseif ($row->isTomorrow())
										<span class="badge badge-secondary">{{ trans('news::news.tomorrow') }}</span>
									@endif
								</li>
								<li class="news-type"><span class="newstype">{{ $type->name }}</span></li>
								@if ($row->location)
									<li class="news-location">{{ $row->location }}</li>
								@endif
								@if ($row->url)
									<li class="news-url"><a href="{{ $row->url }}">{{ $row->url }}</a></li>
								@endif
								<?php
								if (count($row->resources) > 0):
									$resourceArray = array();
									foreach ($row->resources as $resource):
										$resourceArray[] = $resource->resource->name;
									endforeach;
									?>
									<li class="news-tags"><span class="newspostresources">{{ implode(', ', $resourceArray) }}</span></li>
									<?php
								endif;
								?>
								@if (auth()->user()->can('manage news'))
									@php
									$users = $row->associations()
										->where('assoctype', '=', 'user')
										->get();
									@endphp
									@if ($users->count())
										<?php
										$names = array();
										foreach ($users as $usr):
											$u = App\Modules\Users\Models\User::find($usr->associd);
											if (!$u):
												continue;
											endif;
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
										<div class="crmcommentpostedby">Posted by {{ $update->creator ? $update->creator->name : trans('global.unknown') }} on <time datetime="{{ $update->datetimecreated->toDateTimeLocalString() }}">{{ $update->formattedDatetimecreated($update->datetimecreated->toDateTimeString()) }}</time></div>
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