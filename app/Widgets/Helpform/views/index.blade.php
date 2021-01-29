@push('styles')
<link rel="stylesheet" type="text/css" media="all" href="{{ asset('modules/core/vendor/select2/css/select2.css') }}" />
@endpush

@push('scripts')
<script src="{{ asset('modules/core/vendor/select2/js/select2.min.js?v=' . filemtime(public_path() . '/modules/core/vendor/select2/js/select2.min.js')) }}"></script>
@endpush

<form method="post" action="{{ route('page', ['uri' => 'help']) }}">
	<fieldset id="help-cats">
		<legend>Please select a topic</legend>

		@foreach ($topics as $topic)
			<?php
			if (!$topic['title'])
			{
				continue;
			}
			?>
			<div class="form-check">
				<input type="radio" name="category" class="help-cat form-check-input" id="cat_{{ $topic['name'] }}" value="{{ $topic['name'] }}" />
				<label for="cat_{{ $topic['name'] }}" class="form-check-label">{{ $topic['title'] }}</label>
			</div>
		@endforeach

		<div class="form-check">
			<input type="radio" name="category" class="help-cat form-check-input" id="cat_conda" value="other" />
			<label for="cat_other" class="form-check-label">Other...</label>
		</div>

		@foreach ($topics as $topic)
			<?php
			if (!$topic['title'])
			{
				continue;
			}
			?>
			<div id="cat_{{ $topic['name'] }}_faq" class="help-cat-faq mt-2 hide">
				<article class="card">
					<div class="card-body">
						{!! $topic['content'] !!}
					</div>
				</article>
			</div>
		@endforeach

		<div class="form-group hide" id="contact_help">
			<br />
			<p><strong>Didn't find your answer?</strong></p>
			<p><a href="#help-contact" data-hide="#help-cats" class="btn btn-primary btn-contact">Contact support</a></p>
		</div>
	</fieldset>

	<fieldset id="help-contact" class="hide">
		<legend>Please describe the issue</legend>

		<div class="form-group">
			<label for="email">Email address <span class="required-field">*</span></label>
			<input type="email" name="email" id="email" class="form-control" required value="{{ auth()->user() ? auth()->user()->username . '@purdue.edu' : '' }}" />
		</div>

		<div class="form-group">
			<label for="subject">Subject <span class="required-field">*</span></label>
			<input type="text" name="subject" id="subject" class="form-control" required value="" />
		</div>

		<div class="form-group">
			<label for="resource">What RCAC resources does this involve?</label>
			<select class="form-control searchable-select-multi" multiple="multiple" name="resource[]" id="resource">
				<?php
				foreach ($types as $t => $res)
				{
					?>
					<optgroup label="{{ $t }}" class="select2-result-selectable">
						<?php
						foreach ($res as $resource)
						{
							?>
							<option value="{{ $resource->id }}">{{ $resource->name }}</option>
							<?php
						}
						?>
					</optgroup>
					<?php
				}
				?>
			</select>
		</div>

		<div class="form-group">
			<label for="report">Describe the issue <span class="required-field">*</span></label>
			<textarea id="report" name="report" class="form-control" required rows="15" cols="77"></textarea>
			<span class="form-text tex-muted">Please include job IDs (if applicable).</span>
		</div>

		@csrf

		<input type="submit" class="btn btn-primary" id="submitticket" value="Send" />
	</fieldset>
</form>

<script>
$(document).ready(function() {
	$('.help-cat').on('change', function(){
		$('.help-cat-faq').addClass('hide');
		$('#' + $(this).attr('id') + '_faq').removeClass('hide');//.slideDown();
		var label = $(this).parent().find('label')[0];
		$('#contact_help').removeClass('hide');

		if ($(this).data('resource')) {
			$('#resource').val($(this).data('resource'));
		}
		$('#subject').val(label.innerHTML);
	});

	$('.btn-contact').on('click', function(e){
		e.preventDefault();

		$($(this).attr('href')).removeClass('hide');
		$($(this).data('hide')).addClass('hide');

		var rselects = $(".searchable-select-multi");
		if (rselects.length) {
			$(".searchable-select-multi").select2({
				multiple: true,
				closeOnSelect: false,
				templateResult: function(item) {
					if (typeof item.children != 'undefined') {
						var s = $(item.element).find('option').length - $(item.element).find('option:selected').length;
						var el = $('<button class="btn btn-sm btn_select2_optgroup" data-group="' + item.text + '">Select All</span>');

						// Click event
						el.on('click', function (e) {
							e.preventDefault();
							// Select all optgroup child if there aren't, else deselect all
							rselects.find('optgroup[label="' + $(this).data('group') + '"] option').prop(
								'selected',
								$(item.element).find('option').length - $(item.element).find('option:selected').length
							);

							// Trigger change event + close dropdown
							rselects.trigger('change.select2');
							rselects.select2('close');
						});

						var elp = $('<span class="my_select2_optgroup">' + item.text + '</span>');
						elp.append(el);

						return elp;
					}
					return item.text;
				}
			});
		}
	});

	var invalid = false,
		sbmt = $('#submitticket'),
		frm = sbmt.closest('form')[0];
	sbmt.prop('disabled', true);

	var inputs = $('input[required],textarea[required]');
	var needed = inputs.length, validated = 0;
	inputs.on('blur', function(e){
		if (this.value) {
			if (this.validity.valid) {
				this.classList.remove('is-invalid');
				this.classList.add('is-valid');
				validated++;
			} else {
				this.classList.remove('is-valid');
				this.classList.add('is-invalid');
			}
		} else {
			this.classList.remove('is-valid');
			this.classList.add('is-invalid');
		}
		if (needed == validated) {
			sbmt.prop('disabled', false);
		}
	});

	sbmt.on('click', function(e){
		e.preventDefault();

		var elms = frm.querySelectorAll('input[required]');
		elms.forEach(function (el) {
			if (!el.value || !el.validity.valid) {
				el.classList.add('is-invalid');
				invalid = true;
			} else {
				el.classList.remove('is-invalid');
			}
		});
		var elms = frm.querySelectorAll('select[required]');
		elms.forEach(function (el) {
			if (!el.value || el.value <= 0) {
				el.classList.add('is-invalid');
				invalid = true;
			} else {
				el.classList.remove('is-invalid');
			}
		});
		var elms = frm.querySelectorAll('textarea[required]');
		elms.forEach(function (el) {
			if (!el.value || !el.validity.valid) {
				el.classList.add('is-invalid');
				invalid = true;
			} else {
				el.classList.remove('is-invalid');
			}
		});

		if (!invalid) {
			/*$.ajax({
				url: $(frm).attr('action'),
				//url: sbmt.data('api'),
				type: 'post',
				data: $(frm).serialize(),
				dataType: 'json',
				async: false,
				success: function (response) {
					alert('Item added');
				},
				error: function (xhr, ajaxOptions, thrownError) {
					console.log(xhr);
					sbmt.find('.spinner-border').addClass('d-none');
					//Halcyon.message('danger', xhr.responseJSON.message);
				}
			});*/
			frm.submit();
		}
	});
});
</script>