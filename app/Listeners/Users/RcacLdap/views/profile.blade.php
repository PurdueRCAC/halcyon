
	<div class="contentInner">
		<h2>{{ trans('listener.users.rcacldap::rcacldap.title') }}</h2>

		@if (!empty($results))
			<table class="table table-hover">
				<caption>RCAC LDAP</caption>
				<tbody>
					@foreach ($results->getAttributes() as $key => $val)
						<tr>
							<th scope="row">
								{{ $key }}
							</th>
							<td>
								{{ $val }}
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		@endif
	</div>