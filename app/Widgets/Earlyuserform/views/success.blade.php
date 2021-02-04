
<div class="card text-center">
	<div class="card-header text-success">
		<i class="fa fa-check" aria-hidden="true"></i>
	</div>
	<div class="card-body">
		<p><strong>Thank you for your application!</strong></p>
		@if ($params->get('send_confirmation'))
			<p>A confirmation email has been sent to the provided email address.</p>
		@endif
	</div>
</div>
