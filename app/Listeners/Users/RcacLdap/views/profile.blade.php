
	<div class="contentInner">
		<h2>{{ trans('listener.users.rcacldap::rcacldap.title') }}</h2>

		@if (!empty($results))
			<table class="table table-hover">
				<caption class="sr-only">RCAC LDAP Attributes</caption>
				<thead>
					<tr>
						<th scope="col">Attribute</th>
						<th scope="col">Value</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($results->getAttributes() as $key => $val)
						<?php
						if (is_numeric($key))
						{
							continue;
						}
						?>
						<tr>
							<th scope="row">
								{{ $key }}
							</th>
							<td>
								{{ implode('<br />', $val) }}
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		@endif
	</div>