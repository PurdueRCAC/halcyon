
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('core::info.settings') }}</caption>
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
					{{ trans('core::info.safe mode') }}
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::boolean($info['safe_mode']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.open basedir'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::string($info['open_basedir']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.display errors'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::boolean($info['display_errors']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.short open tags'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::boolean($info['short_open_tag']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.file uploads'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::boolean($info['file_uploads']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.magic quotes'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::boolean($info['magic_quotes_gpc']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.register globals'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::boolean($info['register_globals']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.output buffering'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::boolean($info['output_buffering']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.session save path'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::string($info['session.save_path']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.session auto start'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::integer($info['session.auto_start']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.xml enabled'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::set($info['xml']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.zlib enabled'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::set($info['zlib']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.zip enabled'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::set($info['zip']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.disabled functions'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::string($info['disable_functions']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.mbstring enabled'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::set($info['mbstring']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.iconv enabled'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::set($info['iconv']); ?>
				</td>
			</tr>
		</tbody>
	</table>
