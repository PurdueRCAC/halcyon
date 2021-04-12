@extends('layouts.master')

@push('scripts')
<script src="{{ asset('modules/users/js/users.js?v=' . filemtime(public_path() . '/modules/users/js/users.js')) }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('users::users.module name'),
		route('admin.users.index')
	)
	->append(
		$user->name,
		route('admin.users.edit', ['id' => $user->id])
	)
	->append(
		'Permissions',
		route('admin.users.debug', ['id' => $user->id])
	);
@endphp

@section('toolbar')
	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.users.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('users::users.module name') }}: {{ $user->name }}: Permissions
@stop

@section('content')
<form action="{{ route('admin.users.debug', ['id' => $user->id]) }}" method="get" name="adminForm" id="adminForm" class="form-inline">
	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="filter-search col col-xs-12 col-sm-3">
				<div class="form-group">
					<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
					<span class="input-group">
						<input type="text" name="search" id="filter_search" class="form-control filter" placeholder="{{ trans('search.placeholder') }}" value="{{ $filters['search'] }}" />
						<span class="input-group-append"><span class="input-group-text"><span class="icon-search" aria-hidden="true"></span></span></span>
					</span>
				</div>
				<button type="submit" class="btn sr-only">{{ trans('search.submit') }}</button>
			</div>
			<div class="filter-select col col-xs-12 col-sm-7 text-right">
				<label class="sr-only" for="filter_module">{{ trans('users::users.module') }}</label>
				<select name="filter_module" class="form-control filter filter-submit">
					<option value="">{{ trans('users::users.select module') }}</option>
					<?php
					if (count($modules))
					{
						echo Html::select('options', $modules, 'value', 'text', $filters['module']);
					}
					?>
				</select>

				<label class="sr-only" for="filter_level_start">{{ trans('users::users.start level') }}</label>
				<select name="filter_level_start" class="form-control filter filter-submit">
					<option value="">{{ trans('users::users.select level start') }}</option>
					<?php echo Html::select('options', $levels, 'value', 'text', $filters['level_start']); ?>
				</select>

				<label class="sr-only" for="filter_level_end">{{ trans('users::users.end level') }}</label>
				<select name="filter_level_end" class="form-control filter filter-submit">
					<option value="">{{ trans('users::users.select level end') }}</option>
					<?php echo Html::select('options', $levels, 'value', 'text', $filters['level_end']); ?>
				</select>
			</div>
			<div class="filter-select col col-xs-12 col-sm-2">
				<div class="dropdown dropleft">
					<button class="btn btn-secondary dropdown-toggle" type="button" id="legendmenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						{{ trans('users::access.debug.legend') }}
					</button>
					<div class="dropdown-menu" aria-labelledby="legendmenu">
						<span class="dropdown-item">{!! trans('users::access.debug.implicit deny', ['minus' => '<span class="state-no badge badge-warning">-</span>']) !!}</span>
						<span class="dropdown-item">{!! trans('users::access.debug.explicit allow', ['plus' => '<span class="state-yes badge badge-success">&#10003;</span>']) !!}</span>
						<span class="dropdown-item">{!! trans('users::access.debug.explicit deny', ['minus' => '<span class="state-no badge badge-danger">&#10007;</span>']) !!}</span>
					</div>
				</div>
			</div>
		</div>
	</fieldset>

	<div class="card mb-4">
	<table class="table table-hover adminlist">
		<caption class="sr-only">Permissions</caption>
		<thead>
			<tr>
				<th scope="col">
					<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('users::access.id'), 'id', $filters['order_dir'], $filters['order']); ?>
				</th>
				<th scope="col">
					<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('users::access.title'), 'title', $filters['order_dir'], $filters['order']); ?>
				</th>
				<th scope="col">
					<?php echo App\Halcyon\Html\Builder\Grid::sort(trans('users::access.name'), 'name', $filters['order_dir'], $filters['order']); ?>
				</th>
				<?php foreach ($actions as $key => $action) : ?>
					<th scope="col" class="text-center">
						<span class="hasTip" title="<?php echo htmlspecialchars(trans($action[0]), ENT_COMPAT, 'UTF-8'); ?>"><?php echo trans($key); ?></span>
					</th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($assets as $item) : ?>
			<tr>
				<td>
					{{ $item->id }}
				</td>
				<td>
					{{ $item->title }}
				</td>
				<td>
					{!! str_repeat('<span class="gi">|&mdash;</span>', $item->level) !!}
					{{ $item->name }}
				</td>
				<?php
				$checks = $item->checks;
				foreach ($actions as $action) : ?>
					<?php
					$name  = $action[0];
					$check = $checks[$name];
					if ($check === true) :
						$class = 'check-a';
						$text  = '<span class="state-yes badge badge-success">&#10003;</span>';
					elseif ($check === false) :
						$class = 'check-d';
						$text  = '<span class="state-no badge badge-danger">&#10007;</span>';
					elseif ($check === null) :
						$class = 'check-0';
						$text  = '<span class="state-no badge badge-warning">-</span>';
					else :
						$class = '';
						$text  = '&#160;';
					endif;
					?>
					<td class="text-center {{ $class }}">
						{!! $text !!}
					</td>
				<?php endforeach; ?>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	</div>

	{{ $assets->render() }}

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="{{ $filters['order'] }}" />
	<input type="hidden" name="filter_order_dir" value="{{ $filters['order_dir'] }}" />

	@csrf
</form>
@stop