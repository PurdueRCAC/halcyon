@push('scripts')
<script type="text/javascript">
	setmenutype = function(type)
	{
		window.parent.Halcyon.submitbutton('items.setType', type);
		window.parent.$.fancybox.close();
	}
</script>
@endpush

<h2 class="modal-title">{{ trans('menus::menus.TYPE_CHOOSE') }}</h2>
<ul class="menu_types">
	@foreach ($this->types as $name => $list)
		<li>
			<dl class="menu_type">
				<dt>{{ trans($name) }}</dt>
				<dd>
					<ul>
						@foreach ($list as $item)
						<li>
							<a class="choose_type" href="#" title="{{ trans($item->description) }}"
								onclick="javascript:setmenutype('<?php echo base64_encode(json_encode(array('id' => $this->recordId, 'title' => $item->title, 'request' => $item->request))); ?>')">
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
			<dt>{{ trans('menus::menus.TYPE_SYSTEM') }}</dt>
			<dd>
				<ul>
					<li>
						<a class="choose_type" href="#" title="{{ trans('menus::menus.TYPE_EXTERNAL_URL_DESC') }}"
							onclick="javascript:setmenutype('<?php echo base64_encode(json_encode(array('id' => $this->recordId, 'title'=>'url'))); ?>')">
							{{ trans('menus::menus.TYPE_EXTERNAL_URL') }}
						</a>
					</li>
					<li>
						<a class="choose_type" href="#" title="{{ trans('menus::menus.TYPE_ALIAS_DESC') }}"
							onclick="javascript:setmenutype('<?php echo base64_encode(json_encode(array('id' => $this->recordId, 'title'=>'alias'))); ?>')">
							{{ trans('menus::menus.TYPE_ALIAS') }}
						</a>
					</li>
					<li>
						<a class="choose_type" href="#" title="{{ trans('menus::menus.TYPE_SEPARATOR_DESC') }}"
							onclick="javascript:setmenutype('<?php echo base64_encode(json_encode(array('id' => $this->recordId, 'title'=>'separator'))); ?>')">
							{{ trans('menus::menus.TYPE_SEPARATOR') }}
						</a>
					</li>
				</ul>
			</dd>
		</dl>
	</li>
</ul>
