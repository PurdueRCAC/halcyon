
	<table class="table table-hover adminlist">
		<caption class="sr-only visually-hidden">{{ trans('core::info.system information') }}</caption>
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
			<tr>
				<th scope="row">
					{{ trans('core::info.php built on') }}
				</th>
				<td>
					{{ $info['php'] }}
				</td>
			</tr>
			<tr>
				<th scope="row">
					{{ trans('core::info.php version') }}
				</th>
				<td>
					{{ $info['phpversion'] }}
				</td>
			</tr>
			<tr>
				<th scope="row">
					{{ trans('core::info.database version') }}
				</th>
				<td>
					{{ $info['dbversion'] }}
				</td>
			</tr>
			<tr>
				<th scope="row">
					{{ trans('core::info.web server') }}
				</th>
				<td>
					{{ App\Modules\Core\Helpers\Informant::server($info['server']) }}
				</td>
			</tr>
			<tr>
				<th scope="row">
					{{ trans('core::info.web server to php interface') }}
				</th>
				<td>
					{{ $info['sapi_name'] }}
				</td>
			</tr>
			<tr>
				<th scope="row">
					{{ trans('core::info.portal version') }}
				</th>
				<td>
					{{ $info['platform'] . ' ' . $info['version'] }}
				</td>
			</tr>
			<tr>
				<th scope="row">
					{{ trans('core::info.user agent') }}
				</th>
				<td>
					{{ $info['useragent'] }}
				</td>
			</tr>
		</tbody>
	</table>
