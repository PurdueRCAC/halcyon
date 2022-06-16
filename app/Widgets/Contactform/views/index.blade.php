
<form method="post" action="{{ route('page', ['uri' => request()->path()]) }}" class="contactform">
	<fieldset>
		<legend class="sr-only visibly-hidden">{{ trans('widget.contactform::contactform.widget name') }}</legend>

		@if (!empty($errors))
			<div class="alert alert-danger">
				{!! implode('<br />', $errors) !!}
			</div>
		@endif

		@if ($pre_text = $params->get('pre_text'))
			<p>{!! $pre_text !!}</p>
		@endif

		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label for="contact_name{{ $widget->id }}">{{ $params->get('name_label', trans('widget.contactform::contactform.name')) }} <span class="input-required">*</span></label>
					<input type="text" class="form-control" required name="contact[name]" id="contact_name{{ $widget->id }}" value="{{ $data['name'] }}" />
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label for="contact_email{{ $widget->id }}">{{ $params->get('email_label', trans('widget.contactform::contactform.email')) }} <span class="input-required">*</span></label>
					<input type="email" class="form-control" required name="contact[email]" id="contact_email{{ $widget->id }}" value="{{ $data['email'] }}" />
				</div>
			</div>
		</div>

		<div class="form-group">
			<label for="contact_subject{{ $widget->id }}">{{ $params->get('subject_label', trans('widget.contactform::contactform.subject')) }} <span class="input-required">*</span></label>
			<input type="text" class="form-control" required name="contact[subject]" id="contact_subject{{ $widget->id }}" value="{{ $data['subject'] }}" />
		</div>

		<div class="form-group">
			<label for="contact_body{{ $widget->id }}">{{ $params->get('body_label', trans('widget.contactform::contactform.message')) }}</label>
			<textarea class="form-control" name="contact[body]" cols="45" rows="5" id="contact_body{{ $widget->id }}">{{ $data['body'] }}</textarea>
		</div>
	</fieldset>

	@if ($params->get('honeypot'))
		<div class="form-group hide d-none">
			<label for="contact_hp{{ $widget->id }}">{{ trans('widget.contactform::contactform.honeypot') }}</label>
			<input type="text" class="form-control" name="contact[hp]" id="contact_hp{{ $widget->id }}" value="" />
		</div>
	@endif

	@csrf

	<p class="text-center">
		<input type="submit"  class="btn btn-primary" value="{{ $params->get('button_text', trans('widget.contactform::contactform.send')) }}" />
	</p>
</form>
