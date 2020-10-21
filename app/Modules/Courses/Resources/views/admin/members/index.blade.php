@extends('layouts.master')

@section('toolbar')
	@if (auth()->user()->can('create groups'))
		{!! Toolbar::addNew(route('admin.groups.members.create')) !!}
	@endif

	@if (auth()->user()->can('delete groups'))
		{!! Toolbar::deleteList('', route('admin.groups.members.delete')) !!}
	@endif

	@if (auth()->user()->can('admin groups'))
		{!!
			Toolbar::spacer();
			Toolbar::preferences('groups')
		!!}
	@endif

	{!! Toolbar::render() !!}
@stop

@section('title')
{!! config('groups.name') !!}: {{ $group->name }}: Members
@stop

@section('content')
<form action="{{ route('admin.groups.members', ['group' => $group->id]) }}" method="post" name="adminForm" id="adminForm" class="form-inline">

	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="col col-md-4 filter-search">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
			</div>
			<div class="col col-md-8 filter-select text-right">
				<label class="sr-only" for="filter-state">{{ trans('groups::groups.state') }}</label>
				<select name="state" id="filter-state" class="form-control filter filter-submit">
					<option value="*">{{ trans('groups::groups.all states') }}</option>
					<option value="active"<?php if ($filters['state'] == 'active') { echo ' selected="selected"'; } ?>>{{ trans('global.active') }}</option>
					<option value="trashed"<?php if ($filters['state'] == 'trashed') { echo ' selected="selected"'; } ?>>{{ trans('global.trashed') }}</option>
				</select>

				<label class="sr-only" for="filter-type">{{ trans('groups::groups.membership type') }}</label>
				<select name="type" id="filter-type" class="form-control filter filter-submit">
					<option value="0">{{ trans('groups::groups.select membership type') }}</option>
					<?php foreach ($types as $type): ?>
						<option value="<?php echo $type->id; ?>"<?php if ($filters['type'] == $type->id) { echo ' selected="selected"'; } ?>>{{ $type->name }}</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<input type="hidden" name="group" value="{{ $group->id }}" autocomplete="off" />
		<input type="hidden" name="group" value="{{ $filters['group'] }}" />
		<input type="hidden" name="filter_order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="filter_order_dir" value="{{ $filters['order_dir'] }}" />

		<button type="submit" class="btn btn-secondary sr-only">{{ trans('search.submit') }}</button>
	</fieldset>

	<div class="card mb-4">
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('groups::groups.groups') }}</caption>
		<thead>
			<tr>
				<th>
					{!! Html::grid('checkall') !!}
				</th>
				<th scope="col" class="priority-5">
					{!! Html::grid('sort', trans('groups::groups.id'), 'id', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col">
					{!! Html::grid('sort', trans('groups::groups.name'), 'name', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('groups::groups.last visit'), 'last_seen', $filters['order_dir'], $filters['order']) !!}
				</th>
				<th scope="col" class="priority-4">
					{!! Html::grid('sort', trans('groups::groups.type'), 'membertype', $filters['order_dir'], $filters['order']) !!}
				</th>
			</tr>
		</thead>
		<tbody>
		@foreach ($rows as $i => $row)
			<tr<?php if ($row->user && $row->user->trashed()) { echo ' class="trashed"'; } ?>>
				<td>
					@if (auth()->user()->can('edit groups'))
						<span class="form-check"><input type="checkbox" name="id[]" id="cb{{ $i }}" value="{{ $row->id }}" class="form-check-input checkbox-toggle" /><label for="cb{{ $i }}"></label></span>
					@endif
				</td>
				<td class="priority-5">
					@if (auth()->user()->can('edit groups'))
						<a href="{{ route('admin.groups.members.edit', ['id' => $row->id]) }}">
					@endif
							{{ $row->id }}
					@if (auth()->user()->can('edit groups'))
						</a>
					@endif
				</td>
				<td>
					@if ($row->user && $row->user->trashed())
						<span class="icon-alert-triangle glyph warning has-tip" title="{{ trans('groups::groups.user account removed') }}">{{ trans('groups::groups.user account removed') }}</span>
					@endif
					@if (auth()->user()->can('edit users'))
						<a href="{{ route('admin.users.edit', ['id' => $row->userid]) }}">
					@endif
							{{ $row->user ? $row->user->name : trans('global.unknown') . ': ' . $row->userid }}
					@if (auth()->user()->can('edit users'))
						</a>
					@endif
				</td>
				<td class="priority-4">
					<span class="datetime">
						@if ($row->getOriginal('datelastseen') && $row->getOriginal('datelastseen') != '0000-00-00 00:00:00')
							<time datetime="{{ $row->datelastseen }}">{{ $row->datelastseen }}</time>
						@else
							<span class="never">{{ trans('global.never') }}</span>
						@endif
					</span>
				</td>
				<td>
					@if ($row->user && $row->user->trashed())
						{{ $row->type->name }}
					@else
						<?php
						$cls = ($row->membertype == 1) ? 'btn-success' : 'btn-warning';
						$cls = ($row->membertype != 3) ? $cls : 'btn-danger';
						?>
					<div class="btn-group btn-group-sm dropdown" role="group" aria-label="Group membership type">
						<button type="button" class="btn btn-secondary {{ $cls }} dropdown-toggle" id="btnGroupDrop{{ $row->id }}" title="{{ trans('groups::groups.membership type') }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							{{ $row->type->name }}
						</button>
						@if (auth()->user()->can('edit groups'))
							<ul class="dropdown-menu" aria-labelledby="btnGroupDrop{{ $row->id }}">
								@foreach ($types as $type)
									@if ($type->id != $row->membertype && ($type->id == 1 || $type->id == 2))
										<li class="dropdown-item">
											<a class="grid-action" data-id="cb{{ $i }}" href="{{ route('admin.groups.members', ['group' => $row->groupid]) }}">{{ $type->name }}</a>
										</li>
									@endif
								@endforeach
							</ul>
						@endif
					</div>
					@endif
				</td>
			</tr>
		@endforeach
		</tbody>
	</table>
	</div>

	{{ $rows->render() }}

	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />

	@csrf
</form>
@stop
