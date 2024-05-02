<?php
$cls = '';
if (!empty($active)):
	$cls = ' active';
endif;
?>
<div class="media-files media-list{{ $cls }}" id="media-list">
	<div action="{{ route('admin.media.medialist', ['folder' => $folder]) }}" method="post" id="media-form-list" name="media-form-list">
		<div class="manager">
			<table>
				<caption class="sr-only visually-hidden">{{ trans('media::media.files') }}</caption>
				<thead>
					<tr>
						<th scope="col">{{ trans('media::media.list.name') }}</th>
						<th scope="col" class="text-nowrap text-right text-end">{{ trans('media::media.list.size') }}</th>
						<th scope="col">{{ trans('media::media.list.type') }}</th>
						<th scope="col">{{ trans('media::media.list.modified') }}</th>
					@if (auth()->user()->can('manage media'))
						<th scope="col"></th>
					@endif
					</tr>
				</thead>
				<tbody>
					@foreach ($children as $file)
						@if ($file->isDir())
							@include('media::medialist.listfolder')
						@else
							@include('media::medialist.listdoc')
						@endif
					@endforeach
				</tbody>
			</table>

			<!-- <input type="hidden" name="task" value="" />
			<input type="hidden" name="folder" value="{{ $folder }}" /> -->
		</div>
	</div>
</div>
