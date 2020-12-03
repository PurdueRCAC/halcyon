@if (auth()->user()->can('manage users'))

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css') }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
<script>
$(document).ready(function() {
	$('.searchuser').select2({
		ajax: {
			url: "<?php echo route('api.users.index'); ?>",
			dataType: 'json',
			maximumSelectionLength: 1,
			//theme: "classic",
			data: function (params) {
				var query = {
					search: params.term,
					order: 'surname',
					order_dir: 'asc'//,
					//api_token: "<?php echo auth()->user()->api_token; ?>"
				}

				return query;
			},
			processResults: function (data) {
				for (var i = 0; i < data.data.length; i++) {
					data.data[i].text = data.data[i].name + ' (' + data.data[i].username + ')';
				}

				return {
					results: data.data
				};
			}
		}
	});
	$('.searchuser').on('select2:select', function (e) {
		var data = e.params.data;
		window.location = "<?php echo request()->url(); ?>?u=" + data.id;
	});
	$('.searchuser').on('select2:unselect', function (e) {
		var data = e.params.data;
		window.location = "<?php echo request()->url(); ?>";
	});
});
</script>
@endpush

<!-- <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
	<div class="card panel panel-default card-admin">
		<div class="card-body panel-body"> -->
			<form method="get" action="{{ route('site.users.account') }}">
				<label for="newuser" class="sr-only">Show data for user:</label>
				<div class="input-group">
					<select name="u" id="newuser" class="form-control searchuser" multiple="multiple" data-placeholder="Select user...">
						@if ($user->id != auth()->user()->id)
						<option value="{{ $user->id }}" selected="selected">{{ $user->name }}</option>
						@endif
					</select>
					<span class="input-group-addon">
						<span class="input-group-text">
							<i class="fa fa-user" aria-hidden="true" id="add_button_a"></i>
							<input type="submit" class="sr-only" value="Search" />
						</span>
					</span>
				</div>
			</form>
		<!-- </div>
	</div>
</div>-->
@endif
