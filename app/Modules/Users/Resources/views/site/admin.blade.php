@if (auth()->user()->can('manage users'))

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css?v=' . filemtime(public_path() . '/modules/core/vendor/select2/css/select2.css')) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
<script>
$(document).ready(function() {
	var searchusers = $('.searchuser');
	if (searchusers.length) {
		searchusers.each(function(i, el){
			$(el).select2({
				ajax: {
					url: $(el).data('api'),
					dataType: 'json',
					maximumSelectionLength: 1,
					data: function (params) {
						var query = {
							search: params.term,
							order: 'name',
							order_dir: 'asc'
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
		});
		searchusers.on('select2:select', function (e) {
			var data = e.params.data;
			window.location = $(this).data('url') + "?u=" + data.id;
		});
		searchusers.on('select2:unselect', function (e) {
			var data = e.params.data;
			window.location = $(this).data('url');
		});
	}
});
</script>
@endpush

<div class="card panel panel-default panel-info card-admin">
	<div class="card-header panel-heading">
		<form method="get" action="{{ route('site.users.account') }}">
			<label for="newuser" class="sr-only">Show data for user:</label>
			<div class="input-group">
				<select name="u" id="newuser" class="form-control searchuser" multiple="multiple" data-placeholder="Select user..." data-api="{{ route('api.users.index') }}" data-url="{{ request()->url() }}">
					@if ($user->id != auth()->user()->id)
						<option value="{{ $user->id }}" selected="selected">{{ $user->name }}</option>
					@endif
				</select>
				<span class="input-group-append">
					<button type="submit" class="btn input-group-text">
						<i class="fa fa-search" id="add_button_a" aria-hidden="true"></i>
						<span class="sr-only">Search</span>
					</button>
				</span>
			</div>
		</form>
	</div>
</div>
@endif
