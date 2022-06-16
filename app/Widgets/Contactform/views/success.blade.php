
<div class="card text-center">
	<div class="card-header text-success">
		<span class="fa fa-check" aria-hidden="true"></span>
	</div>
	<div class="card-body">
		<p>{!! $this->params->get('thank_you_text', trans('widget.contactform::contactform.thank you')) !!}</p>
		@if ($params->get('send_confirmation'))
			<p>{{ trans('widget.contactform::contactform.confirmation sent') }}</p>
		@endif
	</div>
</div>
