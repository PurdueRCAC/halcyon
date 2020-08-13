<div id="system-messages">
@if (Session::has('success'))
	<div class="alert alert-success" role="alert">
		<!-- <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button> -->
		{{ Session::get('success') }}
	</div>
@endif

@if (Session::has('error'))
	<div class="alert alert-danger" role="alert">
		<!-- <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button> -->
		<ul>

				<li>{{ Session::get('error') }}</li>

		</ul>
	</div>
@endif

@if (Session::has('warning'))
	<div class="alert alert-warning" role="alert">
		<!-- <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button> -->
		{{ Session::get('warning') }}
	</div>
@endif
</div>
