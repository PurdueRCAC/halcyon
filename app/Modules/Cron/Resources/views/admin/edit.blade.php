@extends('layouts.master')

@php
app('request')->merge(['hidemainmenu' => 1]);

app('pathway')
	->append(
		trans('cron::cron.module name'),
		route('admin.cron.index')
	)
	->append(
		($row->id ? trans('global.edit') . ' #' . $row->id : trans('global.create'))
	);
@endphp

@section('toolbar')
	@if (auth()->user()->can('edit cron'))
		{!! Toolbar::save(route('admin.cron.store')) !!}
	@endif

	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.cron.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('cron.name') !!}
@stop

@section('content')
<form action="{{ route('admin.cron.store') }}" method="post" name="adminForm" id="item-form" class="editform form-validate">
	<div class="row">
		<div class="col-md-7">
			<fieldset class="adminform">
				<legend>{{ trans('global.details') }}</legend>

				<div class="form-group">
					<label for="field-command">{{ trans('cron::cron.command') }}: <span class="required">{{ trans('global.required') }}</span></label>
					<select name="fields[command]" class="form-control">
						<option value="">{{ trans('global.none') }}</option>
						<?php foreach ($commands as $command): ?>
							<?php $selected = ($command->getName() == $row->command ? ' selected="selected"' : ''); ?>
							<option value="{{ $command->getName() }}"<?php echo $selected; ?>>{{ $command->getName() }}</option>
						<?php endforeach; ?>
					</select>
					<span class="form-text">{{ trans('cron::cron.command desc') }} </span>
				</div>

				<div class="form-group">
					<label for="field-parameters">{{ trans('cron::cron.parameters') }}:</label>
					<input type="text" name="fields[parameters]" id="field-parameters" class="form-control" maxlength="250" value="{{ $row->parameters }}" placeholder="--foo=bar" />
					<span class="form-text">{{ trans('cron::cron.parameters desc') }}</span>
				</div>

				<div class="form-group">
					<label for="field-description">{{ trans('cron::cron.description') }}:</label>
					<input type="text" name="fields[description]" id="field-description" class="form-control" maxlength="250" value="{{ $row->description }}" />
				</div>
			</fieldset>

			<fieldset class="adminform">
				<legend>{{ trans('cron::cron.recurrence') }}</legend>

				<div class="input-wrap">
					{{ trans('cron::cron.common') }}:<br />
					<select name="fields[recurrence]" id="field-recurrence" class="form-control">
						<option value=""<?php echo ($row->recurrence == '') ? ' selected="selected"' : ''; ?>>{{ trans('cron::cron.option.select') }}</option>
						<option value="custom"<?php echo ($row->recurrence == 'custom') ? ' selected="selected"' : ''; ?>>{{ trans('cron::cron.option.custom') }}</option>
						<option value="0 0 1 1 *"<?php echo ($row->recurrence == '0 0 1 1 *') ? ' selected="selected"' : ''; ?>>{{ trans('cron::cron.option.once a year') }}</option>
						<option value="0 0 1 * *"<?php echo ($row->recurrence == '0 0 1 * *') ? ' selected="selected"' : ''; ?>>{{ trans('cron::cron.option.once a month') }}</option>
						<option value="0 0 * * 0"<?php echo ($row->recurrence == '0 0 * * 0') ? ' selected="selected"' : ''; ?>>{{ trans('cron::cron.option.once a week') }}</option>
						<option value="0 0 * * *"<?php echo ($row->recurrence == '0 0 * * *') ? ' selected="selected"' : ''; ?>>{{ trans('cron::cron.option.once a day') }}</option>
						<option value="0 * * * *"<?php echo ($row->recurrence == '0 * * * *') ? ' selected="selected"' : ''; ?>>{{ trans('cron::cron.option.once an hour') }}</option>
					</select>
				</div>

				<table class="admintable">
					<caption class="sr-only">{{ trans('cron::cron.recurrence') }}</caption>
					<tbody id="custom"<?php echo ($row->isCustomRecurrence()) ? '' : ' class="hidden"'; ?>>
						<tr>
							<th scope="row">
								<label for="field-minute-c">{{ trans('cron::cron.minute') }}</label>:
							</th>
							<td>
								<input type="text" name="fields[minute][c]" id="field-minute-c" class="form-control" value="{{ $row->minute }}" />
							</td>
							<td>
								<select name="fields[minute][s]" id="field-minute-s" class="form-control">
									<option value=""<?php if ($row->minute == '') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.option.custom'); ?></option>
									<option value="*"<?php if ($row->minute == '*') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.option.every'); ?></option>
									<option value="*/5"<?php if ($row->minute == '*/5') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.option.every five'); ?></option>
									<option value="*/10"<?php if ($row->minute == '*/10') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.option.every ten'); ?></option>
									<option value="*/15"<?php if ($row->minute == '*/15') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.option.every fifteen'); ?></option>
									<option value="*/30"<?php if ($row->minute == '*/30') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.option.every thirty'); ?></option>
									<?php for ($i=0, $n=60; $i < $n; $i++) { ?>
										<option value="<?php echo $i; ?>"<?php if ($row->minute == (string) $i) { echo ' selected="selected"'; } ?>><?php echo $i; ?></option>
									<?php } ?>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="field-hour-c">{{ trans('cron::cron.hour') }}</label>:
							</th>
							<td>
								<input type="text" name="fields[hour][c]" id="field-hour-c" class="form-control" value="{{ $row->hour }}" />
							</td>
							<td>
								<select name="fields[hour][s]" id="field-hour-s" class="form-control">
									<option value=""<?php if ($row->hour == '') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.option.custom'); ?></option>
									<option value="*"<?php if ($row->hour == '*') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.option.every'); ?></option>
									<option value="*/2"<?php if ($row->hour == '*/2') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.option.every other'); ?></option>
									<option value="*/4"<?php if ($row->hour == '*/4') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.option.every four'); ?></option>
									<option value="*/6"<?php if ($row->hour == '*/6') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.option.every six'); ?></option>
									<option value="0"<?php if ($row->hour == "0") { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.option.midnight'); ?></option>
									<?php for ($i=1, $n=24; $i < $n; $i++) { ?>
										<option value="<?php echo $i; ?>"<?php if ($row->hour == (string) $i) { echo ' selected="selected"'; } ?>><?php echo $i; ?></option>
									<?php } ?>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="field-day-c">{{ trans('cron::cron.day of month') }}</label>:
							</th>
							<td>
								<input type="text" name="fields[day][c]" id="field-day-c" class="form-control" value="{{ $row->day }}" />
							</td>
							<td>
								<select name="fields[day][s]" id="field-day-s" class="form-control">
									<option value=""<?php if ($row->day == '') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.option.custom'); ?></option>
									<option value="*"<?php if ($row->day == '*') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.option.every'); ?></option>
									<?php for ($i=1, $n=32; $i < $n; $i++) { ?>
										<option value="<?php echo $i; ?>"<?php if ($row->day == (string) $i) { echo ' selected="selected"'; } ?>><?php echo $i; ?></option>
									<?php } ?>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="field-month-c">{{ trans('cron::cron.month') }}</label>:
							</th>
							<td>
								<input type="text" name="fields[month][c]" id="field-month-c" class="form-control" value="{{ $row->month }}" />
							</td>
							<td>
								<select name="fields[month][s]" id="field-month-s" class="form-control">
									<option value=""<?php if ($row->month == '') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.option.custom'); ?></option>
									<option value="*"<?php if ($row->month == '*') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.option.every'); ?></option>
									<option value="*/2"<?php if ($row->month == '*/2') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.option.every other'); ?></option>
									<option value="*/3"<?php if ($row->month == '*/4') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.option.every three'); ?></option>
									<option value="*/6"<?php if ($row->month == '*/6') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.option.every six'); ?></option>
									<option value="1"<?php if ($row->month == '1') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.JANUARY_SHORT'); ?></option>
									<option value="2"<?php if ($row->month == '2') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.FEBRUARY_SHORT'); ?></option>
									<option value="3"<?php if ($row->month == '3') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.MARCH_SHORT'); ?></option>
									<option value="4"<?php if ($row->month == '4') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.APRIL_SHORT'); ?></option>
									<option value="5"<?php if ($row->month == '5') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.MAY_SHORT'); ?></option>
									<option value="6"<?php if ($row->month == '6') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.JUNE_SHORT'); ?></option>
									<option value="7"<?php if ($row->month == '7') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.JULY_SHORT'); ?></option>
									<option value="8"<?php if ($row->month == '8') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.AUGUST_SHORT'); ?></option>
									<option value="9"<?php if ($row->month == '9') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.SEPTEMBER_SHORT'); ?></option>
									<option value="10"<?php if ($row->month == '10') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.OCTOBER_SHORT'); ?></option>
									<option value="11"<?php if ($row->month == '11') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.NOVEMBER_SHORT'); ?></option>
									<option value="12"<?php if ($row->month == '12') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.DECEMBER_SHORT'); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="field-dayofweek-c">{{ trans('cron::cron.day of week') }}</label>:
							</th>
							<td>
								<input type="text" name="fields[dayofweek][c]" id="field-dayofweek-c" class="form-control" value="{{ $row->dayofweek }}" />
							</td>
							<td>
								<select name="fields[dayofweek][s]" id="field-dayofweek-s" class="form-control">
									<option value=""<?php if ($row->dayofweek == '') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.option.custom'); ?></option>
									<option value="*"<?php if ($row->dayofweek == '*') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.option.every'); ?></option>
									<option value="0"<?php if ($row->dayofweek == '0') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.SUN'); ?></option>
									<option value="1"<?php if ($row->dayofweek == '1') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.MON'); ?></option>
									<option value="2"<?php if ($row->dayofweek == '2') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.TUE'); ?></option>
									<option value="3"<?php if ($row->dayofweek == '3') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.WED'); ?></option>
									<option value="4"<?php if ($row->dayofweek == '4') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.THU'); ?></option>
									<option value="5"<?php if ($row->dayofweek == '5') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.FRI'); ?></option>
									<option value="6"<?php if ($row->dayofweek == '6') { echo ' selected="selected"'; } ?>><?php echo trans('cron::cron.SAT'); ?></option>
								</select>
							</td>
						</tr>
					</tbody>
				</table>
			</fieldset>
		</div>
		<div class="col-md-5">
			<fieldset class="adminform">
				<legend>{{ trans('global.publishing') }}</legend>

				<div class="form-group">
					<label for="field-state">{{ trans('cron::cron.state') }}:</label>
					<select class="form-control" name="fields[state]" id="field-state">
						<option value="0"<?php if ($row->state == 0) { echo ' selected="selected"'; } ?>>{{ trans('global.unpublished') }}</option>
						<option value="1"<?php if ($row->state == 1) { echo ' selected="selected"'; } ?>>{{ trans('global.published') }}</option>
					</select>
				</div>

				<div class="form-group">
					<label for="field-dont_overlap">{{ trans('cron::cron.overlap') }}:</label>
					<select class="form-control" name="fields[dont_overlap]" id="field-dont_overlap">
						<option value="0"<?php if ($row->dont_overlap == 0) { echo ' selected="selected"'; } ?>>{{ trans('global.yes') }}</option>
						<option value="1"<?php if ($row->dont_overlap == 1) { echo ' selected="selected"'; } ?>>{{ trans('global.no') }}</option>
					</select>
					<span class="text-muted">{{ trans('cron::cron.overlap desc') }}</span>
				</div>
			</fieldset>

			@if ($row->id)
			<table class="meta">
				<caption class="sr-only">{{ trans('global.metadata') }}</caption>
				<tbody>
					<tr>
						<th scope="row">{{ trans('cron::cron.last run') }}:</th>
						<td>
							@if ($row->ran_at)
								<time datetime="{{ $row->ran_at }}">{{ $row->ran_at }}</time>
							@else
								{{ trans('global.never') }}
							@endif
						</td>
					</tr>
					<tr>
						<th scope="row">{{ trans('cron::cron.next run') }}:</th>
						<td>
							<?php $nxt = $row->nextRun(); ?>
							<?php if ($nxt && $nxt != '0000-00-00 00:00:00') { ?>
								<time datetime="<?php echo $nxt; ?>"><?php echo $nxt; ?></time>
							<?php } else { ?>
								<?php echo $nxt; ?>
							<?php } ?>
						</td>
					</tr>
				</tbody>
			</table>
			@endif
		</div>
	</div>

	@csrf
</form>
@stop