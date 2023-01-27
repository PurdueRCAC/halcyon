@if (auth()->user()->can('manage users'))

@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/tom-select/css/tom-select.bootstrap4.min.css?v=' . filemtime(public_path('/modules/core/vendor/tom-select/css/tom-select.bootstrap4.min.css'))) }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/tom-select/js/tom-select.complete.min.js?v=' . filemtime(public_path('/modules/core/vendor/tom-select/js/tom-select.complete.min.js'))) }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
	document.querySelectorAll('.searchuser').forEach(function(el) {
		var sel = new TomSelect(el, {
			//maxItems: 1,
			valueField: 'id',
			labelField: 'name',
			searchField: ['name', 'username', 'email'],
			plugins: {
				clear_button:{
					title: 'Remove selected',
				}
			},
			persist: false,
			// Fetch remote data
			load: function(query, callback) {
				var url = el.getAttribute('data-api') + '?order=name&order_dir=asc&search=' + encodeURIComponent(query);

				fetch(url)
					.then(response => response.json())
					.then(json => {
						for (var i = 0; i < json.data.length; i++) {
							if (!json.data[i].id) {
								json.data[i].id = json.data[i].username;
							}
						}
						callback(json.data);
					}).catch(() => {
						callback();
					});
			},
			// Custom rendering functions for options and items
			render: {
				// Option list when searching
				option: function(item, escape) {
					if (item.name.match(/\([a-z0-9]+\)$/)) {
						item.username = item.name.replace(/([^\(]+\()/, '').replace(/\)$/, '');
						item.name = item.name.replace(/\s(\([a-z0-9]+\))$/, '');
					}
					if (isNaN(item.id) && typeof item.name != 'undefined') {
						item.disabled = true;
						return `<div data-id="${ escape(item.id) }">${ escape(item.name) } <span class="text-muted">(${ escape(item.username) })</span>
							<span class="text-warning ml-1"><span class="fa fa-exclamation-triangle" aria-hidden="true"></span> ${ escape(el.getAttribute('data-noaccount')) }</span></span>
						</div>`;
					}
					return `<div data-id="${ escape(item.id) }">${ escape(item.name) } <span class="text-muted">(${ escape(item.username) })</span></div>`;
				},
				// Selected items
				item: function(item, escape) {
					if (item.name.match(/\([a-z0-9-]+\)$/)) {
						if (isNaN(item.id)) {
							item.id = item.username;
						}
						item.username = item.name.replace(/([^\(]+\()/, '').replace(/\)$/, '');
						item.name = item.name.replace(/\s(\([a-z0-9-]+\))$/, '');
					}
					return `<div data-id="${ escape(item.id) }">${ escape(item.name) }&nbsp;<span class="text-muted">(${ escape(item.username) })</span></div>`;
					//return `<option value="${ escape(item.id) }">${ escape(item.text) }</option>`;
				}
			}
		});
		sel.on('item_add', function(item) {
			window.location = el.getAttribute('data-url') + '?u=' + item;
		});
		sel.on('item_remove', function(e) {
			window.location = el.getAttribute('data-url');
		});
	});
});
</script>
@endpush

<div class="card card-admin mb-4">
	<div class="card-body">
		<form method="get" action="{{ route('site.users.account') }}" class="row">
		@if (app()->has('impersonate'))
			<div class="col-lg-10 col-md-10 col-sm-12 col-xs-12">
		@else
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		@endif
				<label for="newuser" class="sr-only">Show data for user:</label>
				<div class="input-group">
					<select name="u" id="newuser" class="form-control searchuser" multiple="multiple" data-placeholder="Select user..." data-noaccount="No local account" data-api="{{ route('api.users.index') }}" data-url="{{ request()->url() }}">
						@if ($user->id != auth()->user()->id)
							<option value="{{ $user->id }}" selected="selected">{{ $user->name }} ({{ $user->username }})</option>
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
@endif
