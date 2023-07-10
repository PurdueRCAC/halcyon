@push('scripts')
<script src="{{ timestamped_asset('modules/publications/js/publications.js') }}"></script>
@endpush

<h2 class="modal-title">{{ trans('publications::publications.type choose') }}</h2>
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
			<dt>{{ trans('publications::publications.type system') }}</dt>
			<dd>
				<ul>
					<li>
						<a class="choose_type" href="#" title="{{ trans('publications::publications.type external url desc') }}"
							onclick="javascript:setmenutype('<?php echo base64_encode(json_encode(array('id' => $id, 'title' => 'url'))); ?>')">
							{{ trans('publications::publications.type external url') }}
						</a>
					</li>
					<li>
						<a class="choose_type" href="#" title="{{ trans('publications::publications.type alias desc') }}"
							onclick="javascript:setmenutype('<?php echo base64_encode(json_encode(array('id' => $id, 'title' => 'alias'))); ?>')">
							{{ trans('publications::publications.type alias') }}
						</a>
					</li>
					<li>
						<a class="choose_type" href="#" title="{{ trans('publications::publications.type separator desc') }}"
							onclick="javascript:setmenutype('<?php echo base64_encode(json_encode(array('id' => $id, 'title' => 'separator'))); ?>')">
							{{ trans('publications::publications.type separator') }}
						</a>
					</li>
				</ul>
			</dd>
		</dl>
	</li>
</ul>
