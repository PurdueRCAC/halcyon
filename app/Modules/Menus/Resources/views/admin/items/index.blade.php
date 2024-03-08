@extends('layouts.master')

@push('scripts')
<script src="{{ timestamped_asset('modules/menus/js/menus.js') }}"></script>
@endpush

@php
app('pathway')
	->append(
		trans('menus::menus.module name'),
		route('admin.menus.index')
	)
	->append(
		$menu->title,
		route('admin.menus.items', ['menutype' => $filters['menutype']])
	)
	->append(
		trans('menus::menus.items')
	);
@endphp

@section('toolbar')
	@if ($filters['state'] == 'trashed')
		@if (auth()->user()->can('delete menus'))
			{!! Toolbar::deleteList('', route('admin.menus.items.delete')) !!}
		@endif
		@if (auth()->user()->can('edit.state menus'))
			{!!
				Toolbar::custom(route('admin.menus.items.restore'), 'publish', 'publish', 'Restore', false);
				Toolbar::spacer();
			!!}
		@endif
	@else
		@if (auth()->user()->can('manage menus'))
			{!!
				Toolbar::checkin('admin.menus.checkin');
				Toolbar::spacer();
			!!}
		@endif
		@if (auth()->user()->can('edit.state menus'))
			{!!
				Toolbar::publishList(route('admin.menus.items.publish'));
				Toolbar::unpublishList(route('admin.menus.items.unpublish'));
				Toolbar::spacer();
			!!}
		@endif
		@if (auth()->user()->can('delete menus'))
			{!! Toolbar::deleteList('', route('admin.menus.items.delete')) !!}
		@endif
		@if (auth()->user()->can('create menus'))
			{!! Toolbar::addNew(route('admin.menus.items.create', ['menutype' => $filters['menutype']])) !!}
		@endif
		@if (auth()->user()->can('manage menus'))
			{!!
				Toolbar::spacer();
				Toolbar::link('refresh', 'menus::menus.rebuild', route('admin.menus.rebuild', ['menutype' => $filters['menutype']]));
			!!}
		@endif
		@if (auth()->user()->can('admin menus'))
			{!!
				Toolbar::spacer();
				Toolbar::preferences('menus')
			!!}
		@endif
	@endif

	{!! Toolbar::help('menus::admin.help.items') !!}

	{!! Toolbar::render() !!}
@stop

@section('title')
{{ trans('menus::menus.menu manager') }}: {{ $menu->title }}
@stop

@section('content')
<?php
$saveOrder = ($filters['order'] == 'lft' && $filters['order_dir'] == 'asc');
?>
<form action="{{ route('admin.menus.items') }}" method="get" name="adminForm" id="adminForm">

	<fieldset id="filter-bar" class="container-fluid mb-3">
		<div class="row">
			<div class="col-sm-12 col-md-3 mb-2">
				<label class="sr-only visually-hidden" for="filter_menutype">{{ trans('menus::menus.item.type') }}</label>
				<select name="menutype" id="filter_menutype" class="form-control filter filter-submit">
					@foreach ($menus as $menu)
						<option value="{{ $menu->menutype }}"<?php if ($menu->menutype == $filters['menutype']) { echo ' selected'; } ?>>{{ $menu->title }}</option>
					@endforeach
				</select>
			</div>
			<div class="col-sm-12 col-md-6 mb-2 text-center filter-select">
				<div class="btn-group" role="group" aria-label="{{ trans('widgets::widgets.client type') }}">
					<a href="{{ route('admin.menus.items', ['menutype' => $filters['menutype'], 'state' => '*']) }}" class="btn btn-secondary<?php if (!in_array($filters['state'], ['published', 'unpublished', 'trashed'])): echo ' active'; endif;?>">{{ trans('menus::menus.all states') }}</a>
					<a href="{{ route('admin.menus.items', ['menutype' => $filters['menutype'], 'state' => 'published']) }}" class="btn btn-secondary<?php if ($filters['state'] == 'published'): echo ' active'; endif;?>">{{ trans('global.published') }}</a>
					<a href="{{ route('admin.menus.items', ['menutype' => $filters['menutype'], 'state' => 'unpublished']) }}" class="btn btn-secondary<?php if ($filters['state'] == 'unpublished'): echo ' active'; endif;?>">{{ trans('global.unpublished') }}</a>
					<a href="{{ route('admin.menus.items', ['menutype' => $filters['menutype'], 'state' => 'trashed']) }}" class="btn btn-secondary<?php if ($filters['state'] == 'trashed'): echo ' active'; endif;?>">{{ trans('global.trashed') }}</a>
				</div>
			</div>
			<div class="col-sm-12 col-md-3 mb-2 text-right filter-select">
				<label class="sr-only visually-hidden" for="filter_access">{{ trans('global.access') }}</label>
				<select name="access" id="filter_access" class="form-control filter filter-submit">
					<option value="">{{ trans('menus::menus.all access levels') }}</option>
					@foreach (\App\Halcyon\Html\Builder\Access::assetgroups() as $viewlevel)
						<option value="{{ $viewlevel->value }}"<?php if ($viewlevel->value == $filters['access']) { echo ' selected'; } ?>>{{ $viewlevel->text }}</option>
					@endforeach
				</select>
			</div>
		</div>

		<input type="hidden" name="order" value="{{ $filters['order'] }}" />
		<input type="hidden" name="order_dir" value="{{ $filters['order_dir'] }}" />

		<button class="btn btn-secondary sr-only visually-hidden" type="submit">{{ trans('search.submit') }}</button>
	</fieldset>

	@if (count($rows))
		<?php
		$originalOrders = array();
		$canChange = auth()->user()->can('edit menus');
		$canDelete = auth()->user()->can('delete menus');
		$canEditState = auth()->user()->can('edit.state menus');
		$parent_id = 1;
		$level = 1;
		$p = 1;
		?>
		<div class="container-fluid">
			<ul data-parent="{{ $parent_id }}" id="sortable" data-api="{{ route('api.menus.update', ['id' => $menu->id]) }}">
				<div class="card p-2 mb-1 bg-dark">
					<div class="d-flex">
						@if ($canEditState || $canDelete)
						<div>
							{!! Html::grid('checkall') !!}
						</div>
						@endif
						<div class="w-25 ml-3 ms-3">
							{{ trans('menus::menus.item.title') }}
						</div>
						<div class="flex-grow-1">
							{{ trans('menus::menus.item.link') }}
						</div>
						<div class="text-center" style="width: 10em;">
							{{ trans('menus::menus.state') }}
						</div>
						<div class="text-center" style="width: 10em;">
							{{ trans('menus::menus.access') }}
						</div>
						<div class="text-right">
							<div style="width: 1em;"></div>
						</div>
					</div>
				</div>
		@foreach ($rows as $i => $row)
			<?php
			if ($row->parent_id != $parent_id)
			{
				if ($row->level < $level)
				{
					?>
					</li>
					</ul>
					<?php
				}
				elseif ($row->level > $level)
				{
					?>
					<ul data-parent="{{ $row->parent_id }}" class="ml-3 ms-3">
					<?php
				}
				$parent_id = $row->parent_id;
			}
			else
			{
				?>
				<ul data-parent="{{ $row->parent_id }}" class="ml-3 ms-3"></ul>
				</li>
				<?php
			}
			$level = $row->level;
			?>
			<li data-parent="{{ $row->parent_id }}" data-id="{{ $row->id }}" class="mx-0">
				<div class="card p-2 mb-1{{ !$row->state ? ' bg-transparent' : '' }}{{ $row->trashed() ? ' trashed' : '' }}">
					<div class="d-flex">
						@if ($canEditState || $canDelete)
						<div>
							{!! Html::grid('id', $i, $row->id) !!}
						</div>
						@endif
						<div class="w-25 ml-3 ms-3">
							@if ($row->checked_out)
								<?php echo \App\Halcyon\Html\Builder\Grid::checkedout($i, $row->editor, $row->checked_out_time, 'items.', $canCheckin); ?>
							@endif
							@if (!$row->trashed() && $canChange)
								<a href="{{ route('admin.menus.items.edit', ['id' => $row->id]) }}">
									@if ($row->type == 'separator')
										<span class="unknown">[ {{ $row->type }} ]</span>
									@else
										{{ $row->title }}
									@endif
								</a>
							@else
								{{ $row->title }}
							@endif
						</div>
						<div class="flex-grow-1">
							@if ($row->type == 'separator')
								<div class="smallsub">----</div>
							@elseif ($row->type == 'html')
								<div class="smallsub">&lt;  &gt; ... &lt;/  &gt;</div>
							@else
								<div class="smallsub" title="{{ $row->path }}">
									@if ($row->type != 'url')
										@if (empty($row->note))
											<span class="text-muted">/{{ trim($row->link, '/') }}</span>
										@else
											{{ $row->note }}
										@endif
									@elseif ($row->type == 'url')
										<span class="text-muted">{{ $row->link }}</span>
										@if ($row->note)
											{{ $row->note }}
										@endif
									@endif
								</div>
							@endif
						</div>
						<div class="text-center" style="width: 10em;">
							@if ($row->trashed())
								@if ($canChange)
									<a class="badge badge-danger" href="{{ route('admin.menus.items.restore', ['id' => $row->id]) }}" data-id="cb3" data-task="admin.menus.items.restore" data-tip="{{ trans('menus::menus.restore menu item') }}">
										{{ trans('global.trashed') }}
									</a>
								@else
									<span class="badge badge-danger">
										{{ trans('global.trashed') }}
									</span>
								@endif
							@elseif ($row->state)
								@if ($canChange)
									<a class="badge badge-success" href="{{ route('admin.menus.items.unpublish', ['id' => $row->id]) }}" data-id="cb3" data-task="admin.menus.items.unpublish" data-tip="{{ trans('menus::menus.unpublish menu item') }}">
										{{ trans('global.published') }}
									</a>
								@else
									<span class="badge badge-success">
										{{ trans('global.published') }}
									</span>
								@endif
							@else
								@if ($canChange)
									<a class="badge badge-secondary" href="{{ route('admin.menus.items.publish', ['id' => $row->id]) }}" data-id="cb3" data-task="admin.menus.items.publish" data-tip="{{ trans('menus::menus.publish menu item') }}">
										{{ trans('global.unpublished') }}
									</a>
								@else
									<span class="badge badge-secondary">
										{{ trans('global.unpublished') }}
									</span>
								@endif
							@endif
						</div>
						<div class="text-center" style="width: 10em;">
							<span class="badge access {{ preg_replace('/[^a-z0-9\-_]+/', '', strtolower($row->access_level)) }}">{{ $row->access_level }}</span>
						</div>
						<div class="text-right">
							<div class="draghandle" draggable="true" style="width: 1em;">
								<svg class="draghandle-icon" viewBox="0 0 24 24"><path d="M10,4c0,1.1-0.9,2-2,2S6,5.1,6,4s0.9-2,2-2S10,2.9,10,4z M16,2c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S17.1,2,16,2z M8,10 c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S9.1,10,8,10z M16,10c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S17.1,10,16,10z M8,18 c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S9.1,18,8,18z M16,18c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S17.1,18,16,18z"></path></svg>
							</div>
						</div>
					</div>
				</div>
			</li>
		@endforeach
			</ul>
		</div>
	@else
		<div class="card mb-4">
			<div class="card-body text-muted text-center">{{ trans('global.no results') }}</div>
		</div>
	@endif

	<input type="hidden" name="boxchecked" value="0" />
</form>
@stop