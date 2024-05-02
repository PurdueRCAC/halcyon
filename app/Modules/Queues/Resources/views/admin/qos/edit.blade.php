@extends('layouts.master')

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('queues::queues.module name'),
		route('admin.queues.index')
	)
	->append(
		trans('queues::queues.qos'),
		route('admin.queues.qos')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit queues.qos'))
		{!! Toolbar::save(route('admin.queues.qos.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.queues.qos.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@php
	app('request')->merge(['hidemainmenu' => 1]);
@endphp

@section('title')
{{ trans('queues::queues.module name') }}: {{ trans('queues::queues.qos') }}: {{ $row->id ? trans('global.edit') . ': #' . $row->id : trans('global.create') }}
@stop

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
	var el = document.getElementById('field-preempt_mode');

	if (el) {
		el.addEventListener('keyup', function () {
			if (this.value.includes('CANCEL') || this.value.includes('REQUEUE')) {
				document.querySelectorAll('.preempt_mode_show').forEach(function(item){
					item.classList.remove('d-none');
				});
			} else {
				document.querySelectorAll('.preempt_mode_show').forEach(function(item){
					item.classList.add('d-none');
				});
			}
		});
	}

	var sc = document.getElementById('field-scheduler');

	if (sc) {
		var pmt = document.getElementById('field-preempt');
		var cl = pmt.cloneNode(true);
		cl.id = cl.id + '-clone';
		cl.classList.add('d-none');
		pmt.parentNode.insertBefore(cl, pmt);

		sc.addEventListener('change', function () {
			pmt.innerHTML = '';

			cl.querySelectorAll('optgroup').forEach(function(opt){
				if (opt.id == 'scheduler-' + sc.value) {
					pmt.append(opt.cloneNode(true));
					return;
				}
			});
		});

		pmt.innerHTML = '';

		cl.querySelectorAll('optgroup').forEach(function(opt){
			console.log(opt);
			if (opt.id == 'scheduler-' + sc.value) {
				pmt.append(opt.cloneNode(true));
				return;
			}
		});
	}
});
</script>
@endpush

@section('content')
<form action="{{ route('admin.queues.qos.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">
	@if ($errors->any())
		<div class="alert alert-danger">
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	<div class="row">
		<div class="col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-scheduler">{{ trans('queues::queues.scheduler') }} <span class="required">{{ trans('global.required') }}</span></label>
					<select name="scheduler_id" id="field-scheduler" class="form-control">
						<option value="0">{{ trans('global.none') }}</option>
						<?php foreach ($schedulers as $scheduler): ?>
							<?php $selected = ($scheduler->id == $row->scheduler_id ? ' selected="selected"' : ''); ?>
							<option value="{{ $scheduler->id }}"<?php echo $selected; ?>>{{ $scheduler->hostname }}</option>
						<?php endforeach; ?>
					</select>
				</div>

				<div class="form-group">
					<label for="field-name">{{ trans('queues::queues.name') }} <span class="required">{{ trans('global.required') }}</span></label>
					<input type="text" name="name" id="field-name" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" maxlength="255" required value="{{ $row->name }}" />
					<span class="invalid-feedback">{{ $errors->first('name') }}</span>
				</div>

				<div class="form-group">
					<label for="field-description">{{ trans('queues::queues.description') }}</label>
					<textarea name="description" id="field-description" class="form-control{{ $errors->has('description') ? ' is-invalid' : '' }}" cols="35" rows="3">{{ $row->description }}</textarea>
					<span class="invalid-feedback">{{ $errors->first('description') }}</span>
				</div>
			</fieldset>

			<fieldset class="adminform">
				<legend>{{ trans('queues::queues.limits') }}</legend>

				<fieldset>
					<legend>{{ trans('queues::queues.flags') }}</legend>

					<p class="form-text text-muted">{{ trans('queues::queues.flags desc') }}</p>

					<div class="row">
					@foreach (['DenyOnLimit', 'EnforceUsageThreshold', 'NoReserve', 'PartitionMaxNodes', 'PartitionMinNodes', 'OverPartQOS', 'PartitionTimeLimit', 'RequiresReservation', 'NoDecay', 'UsageFactorSafe'] as $key)
						<div class="col-md-6">
						<div class="form-group">
							<div class="form-check">
								<input type="checkbox" name="flags[]" id="field-flags-{{ $key }}" class="form-check-input" value="{{ $key }}" <?php if (in_array($key, $row->flagsList)) { echo ' checked'; } ?>/>
								<label for="field-flags-{{ $key }}" class="form-check-label">{{ $key }}</label><br />
								<span class="form-text text-muted ml-4">{{ trans('queues::queues.flag ' . strtolower($key) . ' desc') }}</span>
							</div>
						</div>
						</div>
					@endforeach
					</div>
				</fieldset>

				<div class="row">
				@foreach (['priority', 'min_prio_thresh'] as $k)
					<div class="col-md-6">
						<div class="form-group">
							<label for="field-{{ $k }}">{{ trans('queues::queues.' . $k) }}</label>
							<input type="number" name="{{ $k }}" id="field-{{ $k }}" class="form-control{{ $errors->has($k) ? ' is-invalid' : '' }}" value="{{ $row->{$k} }}" />
							<span class="invalid-feedback">{{ $errors->first($k) }}</span>
							<span class="form-text text-muted">{{ trans('queues::queues.' . $k . ' desc') }}</span>
						</div>
					</div>
				@endforeach
				</div>

				<div class="row">
				@foreach (['max_jobs_pa', 'max_jobs_per_user'] as $k)
					<div class="col-md-6">
						<div class="form-group">
							<label for="field-{{ $k }}">{{ trans('queues::queues.' . $k) }}</label>
							<input type="number" name="{{ $k }}" id="field-{{ $k }}" class="form-control{{ $errors->has($k) ? ' is-invalid' : '' }}" value="{{ $row->{$k} }}" />
							<span class="invalid-feedback">{{ $errors->first($k) }}</span>
							<span class="form-text text-muted">{{ trans('queues::queues.' . $k . ' desc') }}</span>
						</div>
					</div>
				@endforeach
				</div>

				<div class="row">
				@foreach (['max_jobs_accrue_pa', 'max_jobs_accrue_pu'] as $k)
					<div class="col-md-6">
						<div class="form-group">
							<label for="field-{{ $k }}">{{ trans('queues::queues.' . $k) }}</label>
							<input type="number" name="{{ $k }}" id="field-{{ $k }}" class="form-control{{ $errors->has($k) ? ' is-invalid' : '' }}" value="{{ $row->{$k} }}" />
							<span class="invalid-feedback">{{ $errors->first($k) }}</span>
							<span class="form-text text-muted">{{ trans('queues::queues.' . $k . ' desc') }}</span>
						</div>
					</div>
				@endforeach
				</div>

				<div class="row">
				@foreach (['max_submit_jobs_pa', 'max_submit_jobs_per_user'] as $k)
					<div class="col-md-6">
						<div class="form-group">
							<label for="field-{{ $k }}">{{ trans('queues::queues.' . $k) }}</label>
							<input type="number" name="{{ $k }}" id="field-{{ $k }}" class="form-control{{ $errors->has($k) ? ' is-invalid' : '' }}" value="{{ $row->{$k} }}" />
							<span class="invalid-feedback">{{ $errors->first($k) }}</span>
							<span class="form-text text-muted">{{ trans('queues::queues.' . $k . ' desc') }}</span>
						</div>
					</div>
				@endforeach
				</div>
			</fieldset>
			<fieldset class="adminform">
				<legend>TRES</legend>

				<p><strong>Maximum Values</strong></p>

				<div class="row">
				@foreach (['max_tres_pa', 'max_tres_pj', 'max_tres_pn', 'max_tres_pu'] as $k)
				<div class="col-md-6">
					<div class="form-group">
						<label for="field-{{ $k }}">{{ trans('queues::queues.' . $k) }}</label>
						<textarea name="{{ $k }}" id="field-{{ $k }}" row="2" cols="35" class="form-control{{ $errors->has($k) ? ' is-invalid' : '' }}">{{ $row->{$k} }}</textarea>
						<span class="invalid-feedback">{{ $errors->first($k) }}</span>
						<span class="form-text text-muted">{{ trans('queues::queues.' . $k . ' desc') }}</span>
					</div>
				</div>
				@endforeach
				</div>

				@foreach (['max_tres_mins_pj', 'max_tres_run_mins_pa', 'max_tres_run_mins_pu'] as $k)
					<div class="form-group">
						<label for="field-{{ $k }}">{{ trans('queues::queues.' . $k) }}</label>
						<span class="input-group">
							<input type="number" name="{{ $k }}" id="field-{{ $k }}" class="form-control{{ $errors->has($k) ? ' is-invalid' : '' }}" min="0" value="{{ $row->{$k} }}" />
							<span class="input-group-append"><span class="input-group-text">{{ trans_choice('global.time.minutes', 2) }}</span></span>
						</span>
						<span class="invalid-feedback">{{ $errors->first($k) }}</span>
						<span class="form-text text-muted">{{ trans('queues::queues.' . $k . ' desc') }}</span>
					</div>
				@endforeach

				<p class="mt-4"><strong>Minimum Values</strong></p>

				@foreach (['min_tres_pj'] as $k)
					<div class="form-group">
						<label for="field-{{ $k }}">{{ trans('queues::queues.' . $k) }}</label>
						<textarea name="{{ $k }}" id="field-{{ $k }}" class="form-control{{ $errors->has($k) ? ' is-invalid' : '' }}">{{ $row->{$k} }}</textarea>
						<span class="invalid-feedback">{{ $errors->first($k) }}</span>
						<span class="form-text text-muted">{{ trans('queues::queues.' . $k . ' desc') }}</span>
					</div>
				@endforeach
			</fieldset>
		</div>
		<div class="col-md-5">
			<fieldset class="adminform">
				<legend>Group</legend>

				@foreach (['max_wall_duration_per_job'] as $k)
					<div class="form-group">
						<label for="field-{{ $k }}">{{ trans('queues::queues.' . $k) }}</label>
						<span class="input-group">
							<input type="number" name="{{ $k }}" id="field-{{ $k }}" class="form-control{{ $errors->has($k) ? ' is-invalid' : '' }}" min="0" value="{{ $row->{$k} }}" />
							<span class="input-group-append"><span class="input-group-text">{{ trans_choice('global.time.minutes', 2) }}</span></span>
						</span>
						<span class="invalid-feedback">{{ $errors->first($k) }}</span>
						<span class="form-text text-muted">{{ trans('queues::queues.' . $k . ' desc') }}</span>
					</div>
				@endforeach

				@foreach (['grp_jobs', 'grp_jobs_accrue', 'grp_submit_jobs'] as $k)
					<div class="form-group">
						<label for="field-{{ $k }}">{{ trans('queues::queues.' . $k) }}</label>
						<input type="number" name="{{ $k }}" id="field-{{ $k }}" class="form-control{{ $errors->has($k) ? ' is-invalid' : '' }}" value="{{ $row->{$k} }}" />
						<span class="invalid-feedback">{{ $errors->first($k) }}</span>
						<span class="form-text text-muted">{{ trans('queues::queues.' . $k . ' desc') }}</span>
					</div>
				@endforeach

				@foreach (['grp_tres'] as $k)
					<div class="form-group">
						<label for="field-{{ $k }}">{{ trans('queues::queues.' . $k) }}</label>
						<input type="text" name="{{ $k }}" id="field-{{ $k }}" class="form-control{{ $errors->has($k) ? ' is-invalid' : '' }}" maxlength="255" value="{{ $row->{$k} }}" />
						<span class="invalid-feedback">{{ $errors->first($k) }}</span>
						<span class="form-text text-muted">{{ trans('queues::queues.' . $k . ' desc') }}</span>
					</div>
				@endforeach

				@foreach (['grp_tres_mins', 'grp_tres_run_mins', 'grp_wall'] as $k)
					<div class="form-group">
						<label for="field-{{ $k }}">{{ trans('queues::queues.' . $k) }}</label>
						<span class="input-group">
							<input type="number" name="{{ $k }}" id="field-{{ $k }}" class="form-control{{ $errors->has($k) ? ' is-invalid' : '' }}" min="0" value="{{ $row->{$k} }}" />
							<span class="input-group-append"><span class="input-group-text">{{ trans_choice('global.time.minutes', 2) }}</span></span>
						</span>
						<span class="invalid-feedback">{{ $errors->first($k) }}</span>
						<span class="form-text text-muted">{{ trans('queues::queues.' . $k . ' desc') }}</span>
					</div>
				@endforeach
			</fieldset>

			<fieldset class="adminform">
				<legend>{{ trans('queues::queues.preempt') }}</legend>

				<p class="form-text text-muted">Preemption is the act of "stopping" one or more "low-priority" jobs to let a "high-priority" job run.</p>

				@foreach (['preempt'] as $k)
					<div class="form-group">
						<label for="field-{{ $k }}">{{ trans('queues::queues.' . $k) }}</label>
						<select name="{{ $k }}" id="field-{{ $k }}" multiple size="5" class="form-control{{ $errors->has($k) ? ' is-invalid' : '' }}">
							@php
							$prev = 0;
							@endphp
							@foreach ($qoses as $qos)
								@if ($qos->scheduler_id != $prev)
									@php
									$prev = $qos->scheduler_id;
									@endphp
								<optgroup id="scheduler-{{ $qos->scheduler_id }}" label="{{ $qos->scheduler->hostname }}">
								@endif
								<option value="{{ $qos->name }}"<?php if (in_array($qos->name, $row->preemptList)) { echo ' selected'; } ?>>{{ $qos->name }}</option>
								@if ($qos->scheduler_id != $prev)
								</optgroup>
								@endif
							@endforeach
						</select>
						<span class="invalid-feedback">{{ $errors->first($k) }}</span>
						<span class="form-text text-muted">{{ trans('queues::queues.' . $k . ' desc') }}</span>
					</div>
				@endforeach

				@foreach (['preempt_mode'] as $k)
					<div class="form-group">
						<label for="field-{{ $k }}">{{ trans('queues::queues.' . $k) }}</label>
						<input type="text" name="{{ $k }}" id="field-{{ $k }}" class="form-control{{ $errors->has($k) ? ' is-invalid' : '' }}" maxlength="255" value="{{ $row->{$k} }}" />
						<span class="invalid-feedback">{{ $errors->first($k) }}</span>
						<span class="form-text text-muted">{{ trans('queues::queues.' . $k . ' desc') }}</span>
					</div>
				@endforeach

				<div class="form-group d-none preempt_mode_show">
					<label for="field-preempt_exempt_time">{{ trans('queues::queues.preempt_exempt_time') }}</label>
					<span class="input-group">
						<input type="number" name="preempt_exempt_time" id="field-preempt_exempt_time" class="form-control" min="0" step="0.25" value="{{ $row->preempt_exempt_time }}" />
						<span class="input-group-append"><span class="input-group-text">{{ trans_choice('global.time.hours', 2) }}</span></span>
					</span>
					<span class="form-text text-muted">{{ trans('queues::queues.preempt_exempt_time desc') }}</span>
				</div>

				@foreach (['grace_time'] as $k)
					<div class="form-group">
						<label for="field-{{ $k }}">{{ trans('queues::queues.' . $k) }}</label>
						<span class="input-group">
							<input type="number" name="{{ $k }}" id="field-{{ $k }}" class="form-control{{ $errors->has($k) ? ' is-invalid' : '' }}" value="{{ $row->{$k} }}" />
							<span class="input-group-append"><span class="input-group-text">{{ trans_choice('global.time.hours', 2) }}</span></span>
						</span>
						<span class="invalid-feedback">{{ $errors->first($k) }}</span>
						<span class="form-text text-muted">{{ trans('queues::queues.' . $k . ' desc') }}</span>
					</div>
				@endforeach
			</fieldset>

			<?php /*<table class="meta table table-bordered">
				<caption class="sr-only visually-hidden">{{ trans('global.metadata') }}</caption>
				<tbody>
					<tr>
						<th scope="row">{{ trans('queues::queues.queues') }}</th>
						<td class="text-right text-end">{{ $row->queues()->count() }}</td>
					</tr>
				</tbody>
			</table>*/ ?>
		</div>
	</div>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop