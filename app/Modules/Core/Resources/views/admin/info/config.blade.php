
	<table class="table table-hover adminlist">
		<caption class="sr-only visually-hidden">{{ trans('core::info.configuration file') }}</caption>
		<thead>
			<tr>
				<th scope="col">
					{{ trans('core::info.setting') }}
				</th>
				<th scope="col">
					{{ trans('core::info.value') }}
				</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($info as $key => $value)
				<tr>
					<td>
						{{ $key }}
					</td>
					<td>
						@if (is_array($value))
							@foreach ($value as $ky => $val)
								@if (is_array($val))
									@foreach ($val as $k => $v)
										{{ $k }} = {{ $v }}<br />
									@endforeach
								@else
									{{ $ky }} = {{ $val }}<br />
								@endif
							@endforeach
						@else
							{{ $value }}
						@endif
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>
