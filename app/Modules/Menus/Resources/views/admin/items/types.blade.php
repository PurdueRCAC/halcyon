@push('scripts')
<script src="{{ asset('modules/menus/js/menus.js?v=' . filemtime(public_path() . '/modules/menus/js/menus.js')) }}"></script>
@endpush

<h2 class="modal-title">{{ trans('menus::menus.type choose') }}</h2>
<ul class="menu_types">
	@foreach ($types as $name => $list)
		<li>
			<dl class="menu_type">
				<dt>{{ trans($name) }}</dt>
				<dd>
					<ul>
						@foreach ($list as $item)
						<li>
							<a class="choose_type" href="#" title="{{ trans($item->description) }}"
								data-type="<?php echo base64_encode(json_encode(array('id' => $id, 'title' => $item->title, 'request' => $item->request))); ?>">
								{{ trans($item->title) }}
							</a>
						</li>
						@endforeach
					</ul>
				</dd>
			</dl>
		</li>
	@endforeach
	<li>
		<dl class="menu_type">
			<dt>{{ trans('menus::menus.type system') }}</dt>
			<dd>
				<ul>
					<li>
						<a class="choose_type" href="#" title="{{ trans('menus::menus.type external url desc') }}"
							onclick="javascript:setmenutype('<?php echo base64_encode(json_encode(array('id' => $id, 'title' => 'url'))); ?>')">
							{{ trans('menus::menus.type external url') }}
						</a>
					</li>
					<li>
						<a class="choose_type" href="#" title="{{ trans('menus::menus.type alias desc') }}"
							onclick="javascript:setmenutype('<?php echo base64_encode(json_encode(array('id' => $id, 'title' => 'alias'))); ?>')">
							{{ trans('menus::menus.type alias') }}
						</a>
					</li>
					<li>
						<a class="choose_type" href="#" title="{{ trans('menus::menus.type separator desc') }}"
							onclick="javascript:setmenutype('<?php echo base64_encode(json_encode(array('id' => $id, 'title' => 'separator'))); ?>')">
							{{ trans('menus::menus.type separator') }}
						</a>
					</li>
				</ul>
			</dd>
		</dl>
	</li>
</ul>
