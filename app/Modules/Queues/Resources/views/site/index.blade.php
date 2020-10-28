@extends('layouts.master')

@push('scripts')
<script src="./js/queue.js"></script>
@endpush

@section('content')
<h2>{!! config('queues.name') !!}</h2>

<form action="{{ url('/queues') }}" method="post" name="adminForm" id="adminForm">

	<fieldset id="filter-bar" class="form-inline">
		<legend>Filter</legend>
		<div class="row">
			<div class="col-sm-6 filter-search span4">
				<div class="form-group">
					<label class="sr-only filter-search-lbl" for="filter_search"><?php echo __('search.label'); ?></label>
					<input type="text" name="filter_search" id="filter_search" class="form-control filter" value="" placeholder="<?php echo __('search.placeholder'); ?>" />
				</div>

				<button type="submit" class="btn btn-default"><?php echo __('search.submit'); ?></button>
			</div>
			<div class="col-sm-6 filter-select span8">
				<div class="form-group">
					<label class="sr-only" for="filter_state"><?php echo __('queues::queues.STATE');?></label>
					<select name="filter_state" class="form-control filter filter-submit">
						<option value="*"><?php echo __('queues::queues.all_states');?></option>
						<option value="active"><?php echo __('queues::queues.ACTIVE');?></option>
						<option value="inactive"><?php echo __('queues::queues.INACTIVE');?></option>
					</select>
				</div>
				<div class="form-group">
					<label class="sr-only" for="filter_type"><?php echo __('queues::queues.TYPE');?></label>
					<select name="filter_type" class="form-control filter filter-submit">
						<option value="0"><?php echo __('queues::queues.TYPE_ALL');?></option>
					</select>
				</div>
			</div>
		</div>
	</fieldset>

	<table class="table table-hover adminlist">
		<thead>
			<tr>
				<th>
					<span class="form-check"><input type="checkbox" name="toggle" value="" id="toggle-all" class="form-check-input checkbox-toggle toggle-all" /><label for="toggle-all"></label></span>
				</th>
				<th scope="col" class="priority-5"><?php echo __('queues::queues.COL_ID'); ?></th>
				<th scope="col"><?php echo __('queues::queues.COL_NAME'); ?></th>
				<th scope="col"><?php echo __('queues::queues.COL_ROLENAME'); ?></th>
				<th scope="col" class="priority-4">{{ __('queues::queues.COL_LISTNAME') }}</th>
				<th scope="col" class="priority-3">{{ __('queues::queues.COL_TYPE') }}</th>
				<th scope="col" class="priority-4">{{ __('queues::queues.COL_CREATED') }}</th>
				<th scope="col" class="priority-2">{{ __('queues::queues.COL_REMOVED') }}</th>
				<th scope="col"><?php echo __('queues::queues.COL_RESOURCES'); ?></th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr>
				<td>
					<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
				</td>
				<td class="priority-5">
					{{ $row->id }}
				</td>
				<td>
					<a href="{{ app('request')->root() }}/admin/queues/edit/{{ $row->id }}">
						{{ $row->name }}
					</a>
				</td>
				<td>
					<a href="{{ app('request')->root() }}/admin/queues/edit/{{ $row->id }}">
						{{ $row->rolename }}
					</a>
				</td>
				<td class="priority-4">
					<a href="{{ app('request')->root() }}/admin/queues/edit/{{ $row->id }}">
						{{ $row->listname }}
					</a>
				</td>
				<td class="priority-3">
					{{ $row->type->name }}
				</td>
				<td class="priority-4">
					<span class="datetime">
						@if ($row->datetimecreated && $row->datetimecreated != '0000-00-00 00:00:00')
							<time datetime="{{ $row->datetimecreated }}">{{ $row->datetimecreated }}</time>
						@else
							<span class="never">{{ __('queues::queues.UNKNOWN') }}</span>
						@endif
					</span>
				</td>
				<td class="priority-4">
					<span class="datetime">
						@if ($row->datetimeremoved && $row->datetimeremoved != '0000-00-00 00:00:00')
							<time datetime="{{ $row->datetimeremoved }}">{{ $row->datetimeremoved }}</time>
						@else
							<span class="never">{{ __('queues::queues.NEVER') }}</span>
						@endif
					</span>
				</td>
				<td class="priority-4">
					<a href="{{ app('request')->root() }}/admin/queues/children/{{ $row->id }}">
						0
					</a>
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>

@stop