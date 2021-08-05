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
							if (data.data[i].id) {
								data.data[i].text = data.data[i].name + ' (' + data.data[i].username + ')';
							} else {
								data.data[i].text = data.data[i].name + ' (' + data.data[i].username + ')';
								data.data[i].id = data.data[i].username;
							}
						}

						return {
							results: data.data
						};
					}
				},
				templateResult: function (state) {
					if (isNaN(state.id) && typeof state.name != 'undefined') {
						return $('<span>' + state.text + ' <span class="text-warning ml-1"><span class="fa fa-exclamation-triangle" aria-hidden="true"></span> No local account</span></span>');
					}
					return state.text;
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

<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
	<div class="card card-admin">
		<div class="card-body">
			<form method="get" action="{{ route('site.users.account') }}" class="row">
			@if (app()->has('impersonate'))
				<div class="col-lg-10 col-md-10 col-sm-12 col-xs-12">
			@else
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			@endif
					<label for="newuser" class="sr-only">Show data for user:</label>
					<div class="input-group">
						<select name="u" id="newuser" class="form-control searchuser" multiple="multiple" data-placeholder="Select user..." data-api="{{ route('api.users.index') }}" data-url="{{ request()->url() }}">
							@if ($user->id != auth()->user()->id)
								<option value="{{ $user->id }}" selected="selected">{{ $user->name }}</option>
							@endif
						</select>
						<span class="input-group-append">
							<button type="submit" class="btn input-group-text">
								<span class="fa fa-search" id="add_button_a" aria-hidden="true"></span>
								<span class="sr-only">Search</span>
							</button>
						</span>
					</div>
			@if (app()->has('impersonate'))
				</div>
				<div class="col-lg-2 col-md-2 col-sm-12 col-xs-12 text-right">
					@if ($user->id == auth()->user()->id || ($user->can('admin') && !auth()->user()->can('admin')) || (app()->has('impersonate') && app('impersonate')->isImpersonating()))
						<a href="#" class="btn btn-secondary impersonate" disabled>Impersonate</a>
					@else
						<a href="{{ route('impersonate', ['id' => $user->id]) }}" class="btn btn-secondary impersonate">Impersonate</a>
					@endif
			@endif
				</div>
				@csrf
			</form>
		</div>
	</div>
</div>
@endif
