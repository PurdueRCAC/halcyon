<?php
$current = URL::current();

$append = '?';
if (strstr($current, '?'))
{
	$append = '&';
}
$route = $current . htmlentities($append) . 'showCaptcha=True';
$route = route('site.core.captcha', ['showCaptcha' => 1])
?>
@push('styles')
<link href="{{ asset('listeners/captcha/image/image.css') }}" />
@endpush

@push('scripts')
<script>
	jQuery(document).ready(function($){
		var el = $($('.captcha-reload')),
			captchaSrc = el.attr('href');
		el.attr('src', captchaSrc + '&time=' + new Date().getTime())
	});
</script>
@endpush

<div class="captcha-block">
	<div class="row">
		<div class="col-md-8">
			<div class="form-group">
				<label for="imgCaptchaTxt">{{ trans('listener.captcha.image::image.enter captcha value') }}</label>
				<span class="input-group">
					<input type="text" class="form-control" name="imgCaptchaTxt" id="imgCaptchaTxt" />
					<span class="input-group-append">
						<span class="input-group-text">
							<a href="#captchaCode" class="captcha-refresh tip" title="{{ trans('listener.captcha.image::image.refresh captcha') }}">
								<span class="fa fa-repeat" aria-hidden="true"></span>
								<span class="sr-only">{{ trans('listener.captcha.image::image.refresh captcha') }}</span>
							</a>
						</span>
					</span>
				</span>
			</div>

			<input type="hidden" name="imgCaptchaTxtInst" id="imgCaptchaTxtInst" value="" />
		</div>
		<div class="col-md-4 text-center">
			<div class="captcha-wrap">
				<img id="captchaCode" src="{{ $route }}" alt="{{ trans('listener.captcha.image::image.image alt') }}" />
			</div>
		</div>
	</div>
</div>
