
	<div class="contentInner">
		<h2>{{ trans('listener.users.footprints::footprints.tickets') }}</h2>

		@if (count($tickets) > 0)
			<table class="table table-hover">
				<caption>Ticket History</caption>
				<thead>
					<tr>
						<th scope="col">ID</th>
						<th scope="col">Submitter</th>
						<th scope="col">Submitted</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($tickets as $ticket)
						<tr>
							<td>
								<a target="_blank" rel="noopener noreferrer" href="https://support.purdue.edu/MRcgi/MRlogin.pl?DL={{ $ticket->id }}DA17">
									Footprints #{{ $ticket->ticketid }}
								</a>
							</td>
							<td>
								{{ $ticket->actor ? $ticket->actor->name : trans('global.unknown') }}
							</td>
							<td>
								{{ $ticket->datetimesubmission->format('M d, Y') }}
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		@else
			<p class="alert alert-info">No tickets found.</p>
		@endif
	</div>