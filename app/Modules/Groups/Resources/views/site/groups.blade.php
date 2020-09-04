
@push('scripts')
<script>
	$(document).ready(function() {
		$('.reveal').on('click', function(e){
			$($(this).data('toggle')).toggleClass('hide');

			var text = $(this).data('text');
			$(this).data('text', $(this).html()); //.replace(/"/, /'/));
			$(this).html(text);
		});

		$('.add-department-row').on('click', function(e){
			e.preventDefault();

			$.post($(this).data('api'), data, function(e){
				var container = $(this).parent().parent();

				var source   = $('#new-department').html(),
					template = Handlebars.compile(source),
					context  = {
						"index" : container.find('li').length
					},
					html = template(context);
				$(this).parent().appendBefore($(this));
			});
		});


		$('#new_group_btn').on('click', function (event) {
			event.preventDefault();

			CreateNewGroup();
		});
		$('#new_group_input').on('keyup', function (event) {
			if (event.keyCode == 13) {
				CreateNewGroup();
			}
		});

		$('#create_gitorg_btn').on('click', function (event) {
			event.preventDefault();
			CreateGitOrg($(this).data('value'));
		});

		$('.add-property').on('click', function(e){
			e.preventDefault();

			AddProperty($(this).data('prop'), $(this).data('value'));
		});
		$('.add-property-input').on('keyup', function(e){
			e.preventDefault();

			if (event.keyCode==13){
				AddProperty($(this).data('prop'), $(this).data('value'));
			}
		});
		$('.edit-property').on('click', function(e){
			e.preventDefault();

			EditProperty($(this).data('prop'), $(this).data('value'));
		});
		$('.edit-property-input').on('keyup', function(event){
			if (event.keyCode==13){
				EditProperty($(this).data('prop'), $(this).data('value'));
			}
		});
		$('.cancel-edit-property').on('click', function(e){
			e.preventDefault();

			CancelEditProperty($(this).data('prop'), $(this).data('value'));
		});
		$('.create-default-unix-groups').on('click', function(e){
			e.preventDefault();
			CreateDefaultUnixGroups($(this).data('value'), $(this).data('group'));
		});
		$('.delete-unix-group').on('click', function(e){
			e.preventDefault();
			DeleteUnixGroup($(this).data('unixgroup'), $(this).data('value'));
		});
	});
</script>
@endpush

	<div class="contentInner">
		@if (auth()->user()->can('create groups'))
			<a class="btn btn-default float-right" href="{{ route('site.users.account.section', ['section' => 'groups']) }}">
				<i class="fa fa-plus-circle"></i> {{ trans('global.create') }}
			</a>
		@endif

		<h2>{{ trans('users::users.groups') }}</h2>

		<div id="everything">
			<ul>
				@foreach ($groups as $g)
				<li>
					<a href="{{ route('site.users.account.section.show', ['section' => 'groups', 'id' => $g->groupid, 'u' => $user->id != auth()->user()->id ? $user->id : null]) }}">
						{{ $g->group->name }}
					</a>
				</li>
				@endforeach
			</ul>

		</div>

	</div>
