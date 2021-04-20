<tr>
	<td class="header">
		<a href="{{ $url }}">
			{{ $slot }}
		</a>
	</td>
</tr>
@if ($alert)
	<tr>
		<td class="alert alert-{{ $alert }}">
		</td>
	</tr>
@endif