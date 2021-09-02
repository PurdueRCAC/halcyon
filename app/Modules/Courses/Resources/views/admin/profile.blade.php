
	<h2 class="sr-only">{{ trans('courses::courses.courses') }}</h2>

	<div id="account-list">
	@if (count($instructing) == 0 && count($student) == 0)
		<div class="card card-placeholder">
			<div class="card-body">
				<h3 class="card-title">What is this page?</h3>
				<p>Here you can find courses that the user us an instructor or attendee for that have been set up with access to <a href="{{ route('site.resources.compute.show', ['name' => 'scholar']) }}">Scholar</a>. When set up, all isntructors and students registered for the course will gain access to Scholar.</p>
			</div>
		</div>
	@else
		<?php
		$now = Carbon\Carbon::now();
		?>
		@if (count($instructing))
			<div class="card">
				<table class="table">
					<caption class="sr-only">Class accounts for {{ $user->name }}</caption>
					<thead>
						<tr>
							<th scope="col">Resource</th>
							<th scope="col">Class</th>
							<th scope="col">Semester</th>
							<th scope="col">Starts</th>
							<th scope="col">Ends</th>
							<th scope="col">Added</th>
							<th scope="col">Removed</th>
						</th>
					</thead>
					<tbody>
						<?php
						$total = 0;

						foreach ($instructing as $class):
							$resource = $class->resource;
							?>
							<tr>
								<td>
									{{ $resource ? $resource->name : trans('global.unknown') }}
								</td>
								<td>
									@if (auth()->user()->can('manage courses'))
										<a href="{{ route('admin.courses.edit', ['id' => $class->id]) }}">
									@endif
									@if ($class->semester == 'Workshop')
										{{ $class->classname }}
									@else
										{{ $class->department . ' ' . $class->coursenumber . ' (' . $class->crn . ')' }}
									@endif
									@if (auth()->user()->can('manage courses'))
										</a>
									@endif
								</td>
								<td>
									{{ $class->semester }}
								</td>
								<td>
									<time datetime="{{ $class->datetimestart->toDateTimeString() }}">
										{{ $class->datetimestart->format('Y-m-d') }}
									</time>
								</td>
								<td>
									<time datetime="{{ $class->datetimestop->toDateTimeString() }}">
										{{ $class->datetimestop->format('Y-m-d') }}
									</time>
								</td>
								<td>
									<time datetime="{{ $class->datetimecreated->toDateTimeString() }}">
										@if ($class->datetimecreated->getTimestamp() > $now->getTimestamp())
											{{ $class->datetimecreated->diffForHumans() }}
										@else
											{{ $class->datetimecreated->format('M d, Y') }}
										@endif
									</time>
								</td>
								<td>
									@if ($class->isTrashed())
										<time datetime="{{ $class->datetimeremoved->toDateTimeString() }}">
											@if ($class->datetimeremoved->getTimestamp() > $now->getTimestamp())
												{{ $class->datetimeremoved->diffForHumans() }}
											@else
												{{ $class->datetimeremoved->format('M d, Y') }}
											@endif
										</time>
									@else
										-
									@endif
								</td>
							</tr>
							<?php
							$total++;
						endforeach;

						if (!$total):
							?>
							<tr>
								<td class="text-center text-muted" colspan="7">
									{{ trans('global.none') }}
								</td>
							</tr>
							<?php
						endif;
						?>
					</tbody>
				</table>
			</div>
		@endif

		@if (count($student))
			<div class="card">
				<table class="table">
					<caption class="sr-only">Class accounts for {{ $user->name }}</caption>
					<thead>
						<tr>
							<th scope="col">Resource</th>
							<th scope="col">Class</th>
							<th scope="col">Semester</th>
							<th scope="col">Starts</th>
							<th scope="col">Ends</th>
							<th scope="col">Added</th>
							<th scope="col">Removed</th>
						</th>
					</thead>
					<tbody>
						<?php
						$total = 0;

						foreach ($student as $s):
							$class = $s->account;

							$resource = $class->resource;
							?>
							<tr>
								<td>
									{{ $resource ? $resource->name : trans('global.unknown') }}
								</td>
								<td>
									@if (auth()->user()->can('manage courses'))
										<a href="{{ route('admin.courses.edit', ['id' => $class->id]) }}">
									@endif
									@if ($class->semester == 'Workshop')
										{{ $class->classname }}
									@else
										{{ $class->department . ' ' . $class->coursenumber . ' (' . $class->crn . ')' }}
									@endif
									@if (auth()->user()->can('manage courses'))
										</a>
									@endif
								</td>
								<td>
									{{ $class->semester }}
								</td>
								<td>
									<time datetime="{{ $class->datetimestart->toDateTimeString() }}">
										{{ $class->datetimestart->format('Y-m-d') }}
									</time>
								</td>
								<td>
									<time datetime="{{ $class->datetimestop->toDateTimeString() }}">
										{{ $class->datetimestop->format('Y-m-d') }}
									</time>
								</td>
								<td>
									<time datetime="{{ $s->datetimecreated->toDateTimeString() }}">
										@if ($s->datetimecreated->getTimestamp() > $now->getTimestamp())
											{{ $s->datetimecreated->diffForHumans() }}
										@else
											{{ $s->datetimecreated->format('M d, Y') }}
										@endif
									</time>
								</td>
								<td>
									@if ($s->isTrashed())
										<time datetime="{{ $s->datetimeremoved->toDateTimeString() }}">
											@if ($s->datetimeremoved->getTimestamp() > $now->getTimestamp())
												{{ $s->datetimeremoved->diffForHumans() }}
											@else
												{{ $s->datetimeremoved->format('M d, Y') }}
											@endif
										</time>
									@else
										-
									@endif
								</td>
							</tr>
							<?php
							$total++;
						endforeach;

						if (!$total):
							?>
							<tr>
								<td class="text-center text-muted" colspan="7">
									{{ trans('global.none') }}
								</td>
							</tr>
							<?php
						endif;
						?>
					</tbody>
				</table>
			</div>
		@endif
	@endif
</div>
