
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('core::info.directory permissions') }}</caption>
		<thead>
			<tr>
				<th scope="col">
					{{ trans('core::info.directory') }}
				</th>
				<th scope="col">
					{{ trans('core::info.status') }}
				</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($info as $dir => $data)
				<tr>
					<td>
						{{ App\Modules\Core\Helpers\Informant::message($dir, $data['message']) }}
					</td>
					<td>
						{!! App\Modules\Core\Helpers\Informant::writable($data['writable']) !!}
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>
