<li class="menu-node">
	<div class="car mb-">
		<div class="d-table">
			<div class="d-row">
				<div class="col-select">
					@if (auth()->user()->can('edit menus'))
						{!! Html::grid('id', $i, $row->id) !!}
					@endif
				</div>
				<!-- <div class="col-id priority-5">
					{{ $row->id }}
				</div> -->
			<div class="col-title">
				
				@if (count($row->children))
					<a href="#children{{ $row->id }}" class="toggle opened" data-target="{{ 'children' . $row->id }}">
						<span class="sr-only">-</span>
					</a>
					@endif

				{!! str_repeat('<span class="gi">|&mdash;</span>', $row->level - 1) !!}
				<!-- <span class="draghandle" draggable="true">
				<svg class="glyph draghandle-icon" viewBox="0 0 24 24"><path d="M10,4c0,1.1-0.9,2-2,2S6,5.1,6,4s0.9-2,2-2S10,2.9,10,4z M16,2c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S17.1,2,16,2z M8,10 c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S9.1,10,8,10z M16,10c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S17.1,10,16,10z M8,18 c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S9.1,18,8,18z M16,18c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S17.1,18,16,18z"></path></svg>
				</span>-->
				@if (!$row->trashed() && auth()->user()->can('edit menus'))
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
			<div class="col-path flex-grow-1">
				@if ($row->type != 'separator')
					<div class="smallsub" title="{{ $row->path }}">
						@if ($row->type != 'url')
							@if (empty($row->note))
								<span class="text-muted">/{{ trim($row->link, '/') }}</span>
							@else
								{!! trans('global.LIST_ALIAS_NOTE', ['alias' => $row->alias, 'note' => $row->note]) !!}
							@endif
						@elseif ($row->type == 'url')
							<span class="text-muted">{{ $row->link }}</span>
							@if ($row->note)
								{!! trans('global.LIST_NOTE', ['note' => $row->note]) !!}
							@endif
						@endif
					</div>
				@else
					<div class="smallsub">
						<span class="text-muted">--</span>
					</div>
				@endif
			</div>
			<div class="col-state text-center priority-3">
				@if ($row->trashed())
					@if (auth()->user()->can('edit menus'))
						<a class="badge badge-danger" href="{{ route('admin.menus.items.restore', ['id' => $row->id]) }}" data-id="cb3" data-task="admin.menus.items.restore" data-tip="{{ trans('menus::menus.restore menu item') }}">
							{{ trans('global.trashed') }}
						</a>
					@else
						<span class="badge badge-danger">
							{{ trans('global.trashed') }}
						</span>
					@endif
				@elseif ($row->state)
					@if (auth()->user()->can('edit menus'))
						<a class="badge badge-success" href="{{ route('admin.menus.items.unpublish', ['id' => $row->id]) }}" data-id="cb3" data-task="admin.menus.items.unpublish" data-tip="{{ trans('menus::menus.unpublish menu item') }}">
							{{ trans('global.published') }}
						</a>
					@else
						<span class="badge badge-success">
							{{ trans('global.published') }}
						</span>
					@endif
				@else
					@if (auth()->user()->can('edit menus'))
						<a class="badge badge-secondary" href="{{ route('admin.menus.items.publish', ['id' => $row->id]) }}" data-id="cb3" data-task="admin.menus.items.publish" data-tip="{{ trans('menus::menus.publish menu item') }}">
							{{ trans('global.unpublished') }}
						</a>
					@else
						<span class="badge badge-secondary">
							{{ trans('global.unpublished') }}
						</span>
					@endif
					<?php //echo App\Modules\Menus\Helpers\Html::state($row->published, $i, $canChange, 'cb'); ?>
				@endif
			</div>
			<div class="col-access text-center priority-4">
				<span class="badge access {{ preg_replace('/[^a-z0-9\-_]+/', '', strtolower($row->access_level)) }}">{{ $row->access_level }}</span>
			</div>
			<div class="col-handle text-right">
			<span class="draghandle" draggable="true">
				<svg class="glyph draghandle-icon" viewBox="0 0 24 24"><path d="M10,4c0,1.1-0.9,2-2,2S6,5.1,6,4s0.9-2,2-2S10,2.9,10,4z M16,2c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S17.1,2,16,2z M8,10 c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S9.1,10,8,10z M16,10c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S17.1,10,16,10z M8,18 c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S9.1,18,8,18z M16,18c-1.1,0-2,0.9-2,2s0.9,2,2,2s2-0.9,2-2S17.1,18,16,18z"></path></svg>
			</span>
			</div>
		</div>
	</div>
	@if (count($row->children))
		@include('menus::admin.items.list', ['rows' => $row->children, 'id' => 'children' . $row->id])
	@endif
</li>