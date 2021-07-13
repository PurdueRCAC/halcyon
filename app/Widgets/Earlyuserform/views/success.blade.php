
<div class="card text-center">
	<div class="card-header text-success">
		<span class="fa fa-check" aria-hidden="true"></span>
	</div>
	<div class="card-body">
		<p><strong>Thank you for your application!</strong></p>
		@if ($params->get('send_confirmation'))
			<p>A confirmation email has been sent to the provided email address.</p>
		@endif
	</div>
</div>
