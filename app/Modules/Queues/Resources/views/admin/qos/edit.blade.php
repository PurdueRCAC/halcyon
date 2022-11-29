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

				<!-- <div class="form-group">
					<label for="field-max_jobs_pa">{{ trans('queues::queues.max_jobs_pa') }}</label>
					<input type="number" name="max_jobs_pa" id="field-max_jobs_pa" class="form-control{{ $errors->has('max_jobs_pa') ? ' is-invalid' : '' }}" value="{{ $row->max_jobs_pa }}" />
					<span class="invalid-feedback">{{ $errors->first('max_jobs_pa') }}</span>
				</div>

				<div class="form-group">
					<label for="field-max_jobs_per_user">{{ trans('queues::queues.max_jobs_per_user') }}</label>
					<input type="number" name="max_jobs_per_user" id="field-max_jobs_per_user" class="form-control{{ $errors->has('max_jobs_per_user') ? ' is-invalid' : '' }}" value="{{ $row->max_jobs_per_user }}" />
					<span class="invalid-feedback">{{ $errors->first('max_jobs_per_user') }}</span>
				</div>

				<div class="form-group">
					<label for="field-max_jobs_accrue_pa">{{ trans('queues::queues.max_jobs_accrue_pa') }}</label>
					<input type="number" name="max_jobs_accrue_pa" id="field-max_jobs_accrue_pa" class="form-control{{ $errors->has('max_jobs_accrue_pa') ? ' is-invalid' : '' }}" value="{{ $row->max_jobs_accrue_pa }}" />
					<span class="invalid-feedback">{{ $errors->first('max_jobs_accrue_pa') }}</span>
				</div>

				<div class="form-group">
					<label for="field-max_jobs_accrue_pu">{{ trans('queues::queues.max_jobs_accrue_pu') }}</label>
					<input type="number" name="max_jobs_accrue_pu" id="field-max_jobs_accrue_pu" class="form-control{{ $errors->has('max_jobs_accrue_pu') ? ' is-invalid' : '' }}" value="{{ $row->max_jobs_accrue_pu }}" />
					<span class="invalid-feedback">{{ $errors->first('max_jobs_accrue_pu') }}</span>
				</div>

				<div class="form-group">
					<label for="field-max_submit_jobs_pa">{{ trans('queues::queues.max_submit_jobs_pa') }}</label>
					<input type="number" name="max_submit_jobs_pa" 	id="field-max_submit_jobs_pa" class="form-control{{ $errors->has('max_submit_jobs_pa') ? ' is-invalid' : '' }}" value="{{ $row->max_submit_jobs_pa }}" />
					<span class="invalid-feedback">{{ $errors->first('max_submit_jobs_pa') }}</span>
				</div>

				<div class="form-group">
					<label for="field-max_submit_jobs_per_user">{{ trans('queues::queues.max_submit_jobs_per_user') }}</label>
					<input type="number" name="max_submit_jobs_per_user" id="field-max_submit_jobs_per_user" class="form-control{{ $errors->has('max_submit_jobs_per_user') ? ' is-invalid' : '' }}" value="{{ $row->max_submit_jobs_per_user }}" />
					<span class="invalid-feedback">{{ $errors->first('max_submit_jobs_per_user') }}</span>
				</div> -->

				@foreach (['max_jobs_pa', 'max_jobs_per_user', 'max_jobs_accrue_pa', 'max_jobs_accrue_pu', 'min_prio_thresh', 'max_submit_jobs_pa', 'max_submit_jobs_per_user'] as $k)
					<div class="form-group">
						<label for="field-{{ $k }}">{{ trans('queues::queues.' . $k) }}</label>
						<input type="number" name="{{ $k }}" id="field-{{ $k }}" class="form-control{{ $errors->has($k) ? ' is-invalid' : '' }}" value="{{ $row->{$k} }}" />
						<span class="invalid-feedback">{{ $errors->first($k) }}</span>
						<span class="form-text text-muted">{{ trans('queues::queues.' . $k . ' desc') }}</span>
					</div>
				@endforeach

				@foreach (['max_tres_pa', 'max_tres_pj', 'max_tres_pn', 'max_tres_pu'] as $k)
					<div class="form-group">
						<label for="field-{{ $k }}">{{ trans('queues::queues.' . $k) }}</label>
						<textarea name="{{ $k }}" id="field-{{ $k }}" class="form-control{{ $errors->has($k) ? ' is-invalid' : '' }}">{{ $row->{$k} }}</textarea>
						<span class="invalid-feedback">{{ $errors->first($k) }}</span>
						<span class="form-text text-muted">{{ trans('queues::queues.' . $k . ' desc') }}</span>
					</div>
				@endforeach

				@foreach (['max_tres_mins_pj', 'max_tres_run_mins_pa', 'max_tres_run_mins_pu'] as $k)
					<?php
					$val = $row->{$k}; // In minutes
					if ($val):
						$val = ($val/60); // Convert to hours
					endif;
					?>
					<div class="form-group">
						<label for="field-{{ $k }}">{{ trans('queues::queues.' . $k) }}</label>
						<span class="input-group">
							<input type="number" name="{{ $k }}" id="field-{{ $k }}" class="form-control{{ $errors->has($k) ? ' is-invalid' : '' }}" min="0" value="{{ $val }}" />
							<span class="input-group-append"><span class="input-group-text">{{ trans_choice('global.time.minutes', 2) }}</span></span>
						</span>
						<span class="invalid-feedback">{{ $errors->first($k) }}</span>
						<span class="form-text text-muted">{{ trans('queues::queues.' . $k . ' desc') }}</span>
					</div>
				@endforeach

				@foreach (['min_tres_pj'] as $k)
					<div class="form-group">
						<label for="field-{{ $k }}">{{ trans('queues::queues.' . $k) }}</label>
						<textarea name="{{ $k }}" id="field-{{ $k }}" class="form-control{{ $errors->has($k) ? ' is-invalid' : '' }}">{{ $row->{$k} }}</textarea>
						<span class="invalid-feedback">{{ $errors->first($k) }}</span>
						<span class="form-text text-muted">{{ trans('queues::queues.' . $k . ' desc') }}</span>
					</div>
				@endforeach

				@foreach (['max_wall_duration_per_job', 'grp_jobs', 'grp_jobs_accrue', 'grp_submit_jobs'] as $k)
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
					<?php
					$val = $row->{$k}; // In minutes
					if ($val):
						$val = ($val/60); // Convert to hours
					endif;
					?>
					<div class="form-group">
						<label for="field-{{ $k }}">{{ trans('queues::queues.' . $k) }}</label>
						<span class="input-group">
							<input type="number" name="{{ $k }}" id="field-{{ $k }}" class="form-control{{ $errors->has($k) ? ' is-invalid' : '' }}" min="0" value="{{ $val }}" />
							<span class="input-group-append"><span class="input-group-text">{{ trans_choice('global.time.minutes', 2) }}</span></span>
						</span>
						<span class="invalid-feedback">{{ $errors->first($k) }}</span>
						<span class="form-text text-muted">{{ trans('queues::queues.' . $k . ' desc') }}</span>
					</div>
				@endforeach

				@foreach (['priority'] as $k)
					<div class="form-group">
						<label for="field-{{ $k }}">{{ trans('queues::queues.' . $k) }}</label>
						<input type="number" name="{{ $k }}" id="field-{{ $k }}" class="form-control{{ $errors->has($k) ? ' is-invalid' : '' }}" value="{{ $row->{$k} }}" />
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
							@foreach ($qoses as $qos)
								<option value="{{ $qos->name }}"<?php if (in_array($qos->name, $row->preemptList)) { echo ' selected'; } ?>>{{ $qos->name }}</option>
							@endforeach
						</select>
						<span class="invalid-feedback">{{ $errors->first($k) }}</span>
						<span class="form-text text-muted">{{ trans('queues::queues.' . $k . ' desc') }}</span>
					</div>
				@endforeach

				<?php /*<fieldset>
					<legend>{{ trans('queues::queues.preempt_mode') }}</legend>

					<p class="form-text text-muted">{{ trans('queues::queues.preempt_mode desc') }}</p>

					<div class="form-group mb-0">
					<div class="form-check">
						<input type="checkbox" name="preempt_mode[]" id="field-preempt_mode-off" class="form-check-input" value="OFF" <?php if (in_array('OFF', $row->preemptModeList)) { echo ' checked'; } ?>/>
						<label for="field-preempt_mode-off" class="form-check-label">OFF</label>
					</div>
					</div>

					<div class="form-group mb-0">
					<div class="form-check">
						<input type="checkbox" name="preempt_mode[]" id="field-preempt_mode-cancel" class="form-check-input preempt_mode_time" value="CANCEL" <?php if (in_array('CANCEL', $row->preemptModeList)) { echo ' checked'; } ?>/>
						<label for="field-preempt_mode-cancel" class="form-check-label">CANCEL</label>
					</div>
					</div>

					<div class="form-group mb-0">
					<div class="form-check">
						<input type="checkbox" name="preempt_mode[]" id="field-preempt_mode-gang" class="form-check-input" value="GANG" <?php if (in_array('GANG', $row->preemptModeList)) { echo ' checked'; } ?>/>
						<label for="field-preempt_mode-gang" class="form-check-label">GANG</label>
					</div>
					</div>

					<div class="form-group mb-0">
					<div class="form-check">
						<input type="checkbox" name="preempt_mode[]" id="field-preempt_mode-requeue" class="form-check-input preempt_mode_time" value="REQUEUE" <?php if (in_array('REQUEUE', $row->preemptModeList)) { echo ' checked'; } ?>/>
						<label for="field-preempt_mode-requeue" class="form-check-label">REQUEUE</label>
					</div>
					</div>

					<div class="form-group">
					<div class="form-check">
						<input type="checkbox" name="preempt_mode[]" id="field-preempt_mode-suspend" class="form-check-input" value="SUSPEND" <?php if (in_array('SUSPEND', $row->preemptModeList)) { echo ' checked'; } ?>/>
						<label for="field-preempt_mode-suspend" class="form-check-label">SUSPEND</label>
					</div>
					</div>
				</fieldset>*/ ?>
				@foreach (['preempt_mode'] as $k)
					<div class="form-group">
						<label for="field-{{ $k }}">{{ trans('queues::queues.' . $k) }}</label>
						<input type="text" name="{{ $k }}" id="field-{{ $k }}" class="form-control{{ $errors->has($k) ? ' is-invalid' : '' }}" maxlength="255" value="{{ $row->{$k} }}" />
						<span class="invalid-feedback">{{ $errors->first($k) }}</span>
						<span class="form-text text-muted">{{ trans('queues::queues.' . $k . ' desc') }}</span>
					</div>
				@endforeach

				<div class="form-group d-none preempt_mode_show">
					<?php
					$val = $row->preempt_exempt_time; // In minutes
					if ($val):
						$val = ($val/60); // Convert to hours
					endif;
					?>
					<label for="field-preempt_exempt_time">{{ trans('queues::queues.preempt_exempt_time') }}</label>
					<span class="input-group">
						<input type="number" name="preempt_exempt_time" id="field-preempt_exempt_time" class="form-control" min="0" step="0.25" value="{{ $val }}" />
						<span class="input-group-append"><span class="input-group-text">{{ trans_choice('global.time.hours', 2) }}</span></span>
					</span>
					<span class="form-text text-muted">{{ trans('queues::queues.preempt_exempt_time desc') }}</span>
				</div>

				@foreach (['grace_time'] as $k)
					<?php
					$val = $row->{$k}; // In minutes
					if ($val):
						$val = ($val/60); // Convert to hours
					endif;
					?>
					<div class="form-group">
						<label for="field-{{ $k }}">{{ trans('queues::queues.' . $k) }}</label>
						<span class="input-group">
							<input type="number" name="{{ $k }}" id="field-{{ $k }}" class="form-control{{ $errors->has($k) ? ' is-invalid' : '' }}" value="{{ $val }}" />
							<span class="input-group-append"><span class="input-group-text">{{ trans_choice('global.time.hours', 2) }}</span></span>
						</span>
						<span class="invalid-feedback">{{ $errors->first($k) }}</span>
						<span class="form-text text-muted">{{ trans('queues::queues.' . $k . ' desc') }}</span>
					</div>
				@endforeach
			</fieldset>
		</div>
		<div class="col-md-5">
			<table class="meta table table-bordered">
				<caption class="sr-only">{{ trans('global.metadata') }}</caption>
				<tbody>
					<tr>
						<th scope="row">{{ trans('queues::queues.queues') }}</th>
						<td class="text-right">{{ $row->queues()->count() }}</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	<input type="hidden" name="id" id="field-id" value="{{ $row->id }}" />

	@csrf
</form>
@stop