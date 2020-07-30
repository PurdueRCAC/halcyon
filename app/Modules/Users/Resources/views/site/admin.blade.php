@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css') }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
<script>
$(document).ready(function() {
	$('.searchuser').select2({
		ajax: {
			url: "<?php echo url('/'); ?>/api/users/",
			dataType: 'json',
			maximumSelectionLength: 1,
			//theme: "classic",
			data: function (params) {
				var query = {
					search: params.term,
					order: 'surname',
					order_dir: 'asc',
					api_token: "<?php echo auth()->user()->api_token; ?>"
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
		window.location = "<?php echo request()->url(); ?>?id=" + data.id;
	});
	$('.searchuser').on('select2:unselect', function (e) {
		var data = e.params.data;
		window.location = "<?php echo request()->url(); ?>";
	});
});
</script>
@endpush


@if (auth()->user()->can('manage users'))
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
	<div class="card panel panel-default card-admin">
		<!-- <div class="card-header panel-heading">
			Admin Options
		</div> -->
		<div class="card-body panel-body">
			<form method="get" action="{{ route('site.users.account') }}" class="row">
					<label for="newuser" class="col col-md-3">Show data for user:</label>
					<div class="col col-md-9 input-group">
						<!--<input type="text" name="newuser" id="newuser" class="form-control searchuser" placeholder="Search for someone..." autocorrect="off" autocapitalize="off" />
						<div id="user_results" class="searchMain usersearch_results"></div> -->
						<select name="newuser" id="newuser" class="form-control searchuser" multiple="multiple" data-placeholder="Select user...">
							@if ($user->id != auth()->user()->id)
							<option value="{{ $user->id }}" selected="selected">{{ $user->name }}</option>
							@endif
						</select>
						<span class="input-group-addon">
							<span class="input-group-text">
								<i class="fa fa-search" aria-hidden="true" id="add_button_a"></i>
							</span>
						</span>
					</div>
					<!--
					@if ($user->id != auth()->user()->id)
						<p>
							Showing information for "{{ $user->name }}":
						</p>
					@endif
					-->
			</form>
		</div>
	</div>
</div>
@endif
