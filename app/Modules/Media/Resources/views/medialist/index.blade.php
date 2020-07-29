<?php $base = url('/'); ?>

<?php ($active = $layout == 'list' ? true : false); ?>
@include('media::medialist.list')

<?php ($active = $layout == 'thumbs' ? true : false); ?>
@include('media::medialist.thumbs')

<div class="spinner d-none">
<div class="spinner-border" role="status">
	<span class="sr-only">Loading...</span>
</div>
</div>