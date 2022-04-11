<?php
/**
 * Publications list
 */
?>
<div class="publications">
	@if (count($rows))
		<ul>
			@foreach ($rows as $i => $row)
			<li>
				<div id="publication{{ $row->id }}" class="publication">
					{!! $row->toHtml() !!}
				</div>
			</li>
			@endforeach
		</ul>

		{{ $rows->render() }}
	@else
		<div class="placeholder card text-center">
			<div class="placeholder-body card-body">
				<span class="fa fa-ban" aria-hidden="true"></span>
				<p>{{ trans('global.no results') }}</p>
			</div>
		</div>
	@endif
</div>
