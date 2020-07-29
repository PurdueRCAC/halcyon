<!-- <fieldset class="adminform">
	<legend>{{ trans('core::info.system information') }}</legend> -->
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('core::info.settings') }}</caption>
		<thead>
			<tr>
				<th scope="col">
					{{ trans('core::info.SETTING') }}
				</th>
				<th scope="col">
					{{ trans('core::info.VALUE') }}
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
					{{ trans('core::info.DATABASE_VERSION') }}
				</th>
				<td>
					{{ $info['dbversion'] }}
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
					{{ trans('core::info.WEB_SERVER') }}
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::server($info['server']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					{{ trans('core::info.WEBSERVER_TO_PHP_INTERFACE') }}
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
					{{ $info['version'] }}
				</td>
			</tr>
			<tr>
				<th scope="row">
					{{ trans('core::info.PLATFORM_VERSION') }}
				</th>
				<td>
					{{ $info['platform'] }}
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
<!-- </fieldset> -->
