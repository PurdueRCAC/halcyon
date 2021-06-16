
@push('scripts')
<script src="{{ asset('modules/queues/js/site.js?v=' . filemtime(public_path() . '/modules/queues/js/site.js')) }}"></script>
@endpush

<table class="table table-hover">
	<caption class="sr-only">Below is a list of all queues:</caption>
	<thead>
		<tr>
			<th scope="col" class="text-center">{{ trans('queues::queues.state') }}</th>
			<th scope="col">{{ trans('queues::queues.resource') }}</th>
			<th scope="col">{{ trans('queues::queues.name') }}</th>
			<th scope="col" class="text-right">{{ trans('queues::queues.cores') }}</th>
			<th scope="col" class="text-right">{{ trans('queues::queues.nodes') }}</th>
			<th scope="col" class="text-right">{{ trans('queues::queues.walltime') }}</th>
			@if (auth()->user()->can('edit.state queues'))
			<th scope="col">{{ trans('queues::queues.options') }}</th>
			@endif
			@if (auth()->user()->can('delete queues'))
			<th scope="col"></th>
			@endif
		</tr>
	</thead>
	<tbody>
		<?php
		$canManage = auth()->user()->can('edit groups') || (auth()->user()->can('edit.own groups') && $group->ownerid == auth()->user()->id);

		$q = (new App\Modules\Queues\Models\Queue)->getTable();
		$s = (new App\Modules\Queues\Models\Scheduler)->getTable();
		$r = (new App\Modules\Resources\Models\Subresource)->getTable();

		$queues = $group->queues()
			->select($q . '.*')
			->join($s, $s . '.id', $q . '.schedulerid')
			->join($r, $r . '.id', $q . '.subresourceid')
			->where(function($where) use ($s)
			{
				$where->whereNull($s . '.datetimeremoved')
					->orWhere($s . '.datetimeremoved', '=', '0000-00-00 00:00:00');
			})
			->where(function($where) use ($r)
			{
				$where->whereNull($r . '.datetimeremoved')
					->orWhere($r . '.datetimeremoved', '=', '0000-00-00 00:00:00');
			})
			->withTrashed()
			->whereIsActive()
			->orderBy($r . '.name', 'asc')
			->orderBy($q . '.name', 'asc')
			->get();

		if (count($queues) > 0)
		{
			foreach ($queues as $q)
			{
				if (!$canManage && !$q->users()->where('userid', '=', auth()->user()->id)->count())
				{
					continue;
				}
				?>
				<tr>
					<td class="text-center">
						@if ($q->enabled && $q->started && $q->active)
							@if ($q->reservation)
								<span class="text-info tip" title="{{ trans('Queue has dedicated reservation.') }}">
									<i class="fa fa-circle" aria-hidden="true"></i><span class="sr-only">{{ trans('Queue has dedicated reservation.') }}</span>
								</span>
							@else
								<span class="text-success tip" title="{{ trans('Queue is running.') }}">
									<i class="fa fa-check" aria-hidden="true"></i><span class="sr-only">{{ trans('Queue is running.') }}</span>
								</span>
							@endif
						@elseif ($q->active)
							<span class="text-danger tip" title="{{ trans('Queue is stopped or disabled.') }}">
								<i class="fa fa-minus-circle" aria-hidden="true"></i><span class="sr-only">{{ trans('Queue is stopped or disabled.') }}</span>
							</span>
						@elseif (!$q->active)
							<span class="text-warning tip" title="{{ trans('Queue has no active resources. Remove queue or sell/loan nodes.') }}">
								<i class="fa fa-exclamation-triangle" aria-hidden="true"></i><span class="sr-only">{{ trans('Queue has no active resources. Remove queue or sell/loan nodes.') }}</span>
							</span>
						@endif
					</td>
					<td>
						{{ $q->subresource ? $q->subresource->name : '' }}
					</td>
					<td>
						@if (auth()->user()->can('manage queues'))
							<a href="{{ route('admin.queues.edit', ['id' => $q->id]) }}">
								{{ $q->name }}
							</a>
						@else
							{{ $q->name }}
						@endif
					</td>
					<td class="text-right">
						{{ $q->totalcores }}
					</td>
					<td class="text-right">
						@if ($q->subresource && $q->subresource->nodecores > 0)
							{{ round($q->totalcores/$q->subresource->nodecores, 1) }}
						@endif
					</td>
					<td class="text-right">
						<?php
						if (count($q->walltimes) > 0)
						{
							$walltime = $q->walltimes->first()->walltime;
							$unit = '';
							if ($walltime < 60)
							{
								$unit = 'sec';
							}
							elseif ($walltime < 3600)
							{
								$walltime /= 60;
								$unit = 'min';
							}
							elseif ($walltime < 86400)
							{
								$walltime /= 3600;
								$unit = 'hrs';
							}
							else
							{
								$walltime /= 86400;
								$unit = 'days';
							}
							echo $walltime . ' ' . $unit;
						}
						?>
					</td>
					@if (auth()->user()->can('edit.state queues'))
					<td>
						@if ($q->enabled)
							<a class="set-queue-status btn-enable"
								href="{{ route('admin.queues.disable', ['id' => $q->id]) }}"
								data-api="{{ route('api.queues.update', ['id' => $q->id]) }}"
								data-queue="{{ $q->id }}"
								data-status="0"
								title="{{ trans('queues::queues.stop scheduling') }}">
								<i class="fa fa-ban" aria-hidden="true"></i> {{ trans('queues::queues.stop scheduling') }}
							</a>
						@else
							<a class="set-queue-status btn-disable"
								href="{{ route('admin.queues.enable', ['id' => $q->id]) }}"
								data-api="{{ route('api.queues.update', ['id' => $q->id]) }}"
								data-queue="{{ $q->id }}"
								data-status="1"
								title="{{ trans('queues::queues.start scheduling') }}">
								<i class="fa fa-check" aria-hidden="true"></i> {{ trans('queues::queues.start scheduling') }}
							</a>
						@endif
					</td>
					@endif
					@if (auth()->user()->can('delete queues'))
					<td class="text-right">
						<a class="delete-queue btn text-danger"
							href="{{ route('admin.queues.delete', ['id' => $q->id]) }}"
							data-api="{{ route('api.queues.delete', ['id' => $q->id]) }}"
							data-queue="{{ $q->id }}"
							data-confirm="Are you sure you want to delete this queue?"
							title="{{ trans('global.button.delete') }}">
							<i class="fa fa-trash" aria-hidden="true"></i><span class="sr-only">{{ trans('global.button.delete') }}</span>
						</a>
					</td>
					@endif
				</tr>
			<?php } ?>
		<?php } else { ?>
			<tr>
				<td colspan="{{ auth()->user()->can('edit.state queues') ? 7 : 6 }}" class="text-center">
					<span class="none">No queues found.</span>
				</td>
			</tr>
		<?php } ?>
	</tbody>
</table>

