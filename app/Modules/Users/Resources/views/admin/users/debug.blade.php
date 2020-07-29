@extends('layouts.master')

@push('scripts')
<script src="{{ asset('js/validate.js?v=' . filemtime(public_path() . '/js/validate.js')) }}"></script>
<script src="{{ asset('modules/users/js/users.js?v=' . filemtime(public_path() . '/modules/users/js/users.js')) }}"></script>
@endpush

@section('toolbar')
	{!!
		Toolbar::spacer();
		Toolbar::cancel(route('admin.users.cancel'));
	!!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('users::system.users') }}: {{ $user->name }}: Permissions
@stop

@section('content')
<form action="{{ route('admin.users.debug', ['id' => $user->id]) }}" method="get" name="adminForm" id="adminForm" class="form-inline">
	<fieldset id="filter-bar" class="container-fluid">
		<div class="row">
			<div class="filter-search col col-xs-12 col-sm-5">
				<label class="sr-only" for="filter_search">{{ trans('search.label') }}</label>
				<input type="text" name="filter_search" id="filter_search" class="form-control filter" value="{{ $filters['search'] }}" placeholder="{{ trans('search.placeholder') }}" />
				<button type="submit" class="btn">{{ trans('search.submit') }}</button>
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
		</div>
	</fieldset>

	<table class="table table-hover adminlist">
		<caption>
			<?php echo trans('users::access.DEBUG_LEGEND'); ?>
			<span class="swatch"><?php echo trans('users::access.DEBUG_NO_CHECK', ['minus' => '-']);?></span>
			<span class="check-0 swatch"><?php echo trans('users::access.DEBUG_IMPLICIT_DENY', ['minus' => '-']);?></span>
			<span class="check-a swatch"><?php echo trans('users::access.DEBUG_EXPLICIT_ALLOW', ['plus' => '&#10003;']);?></span>
			<span class="check-d swatch"><?php echo trans('users::access.DEBUG_EXPLICIT_DENY', ['minus' => '&#10007;']);?></span>
		</caption>
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
					<th>
						<span class="hasTip" title="<?php echo htmlspecialchars(trans($action[0]), ENT_COMPAT, 'UTF-8'); ?>"><?php echo trans($key); ?></span>
					</th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($assets as $item) : ?>
			<tr class="row0">
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
						$text  = '<span class="state yes">&#10003;</span>';
					elseif ($check === false) :
						$class = 'check-d';
						$text  = '<span class="state no">&#10007;</span>';
					elseif ($check === null) :
						$class = 'check-0';
						$text  = '<span class="state no">-</span>';
					else :
						$class = '';
						$text  = '&#160;';
					endif;
					?>
					<td class="center <?php echo $class; ?>">
						<?php echo $text; ?>
					</td>
				<?php endforeach; ?>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	{{ $assets->render() }}

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="{{ $filters['order'] }}" />
	<input type="hidden" name="filter_order_dir" value="{{ $filters['order_dir'] }}" />

	@csrf
</form>
@stop