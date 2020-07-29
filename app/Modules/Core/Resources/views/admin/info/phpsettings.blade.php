
	<table class="table table-hover adminlist">
		<caption class="sr-only">{{ trans('core::info.settings') }}</caption>
		<thead>
			<tr>
				<th scope="col">
					<?php echo trans('core::info.SETTING'); ?>
				</th>
				<th scope="col">
					<?php echo trans('core::info.VALUE'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.SAFE_MODE'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::boolean($info['safe_mode']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.OPEN_BASEDIR'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::string($info['open_basedir']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.DISPLAY_ERRORS'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::boolean($info['display_errors']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.SHORT_OPEN_TAGS'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::boolean($info['short_open_tag']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.FILE_UPLOADS'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::boolean($info['file_uploads']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.MAGIC_QUOTES'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::boolean($info['magic_quotes_gpc']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.REGISTER_GLOBALS'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::boolean($info['register_globals']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.OUTPUT_BUFFERING'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::boolean($info['output_buffering']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.SESSION_SAVE_PATH'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::string($info['session.save_path']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.SESSION_AUTO_START'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::integer($info['session.auto_start']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.XML_ENABLED'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::set($info['xml']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.ZLIB_ENABLED'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::set($info['zlib']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.ZIP_ENABLED'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::set($info['zip']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.DISABLED_FUNCTIONS'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::string($info['disable_functions']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.MBSTRING_ENABLED'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::set($info['mbstring']); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php echo trans('core::info.ICONV_AVAILABLE'); ?>
				</th>
				<td>
					<?php echo App\Modules\Core\Helpers\Informant::set($info['iconv']); ?>
				</td>
			</tr>
		</tbody>
	</table>
