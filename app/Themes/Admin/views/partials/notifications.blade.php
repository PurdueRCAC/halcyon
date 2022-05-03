<div id="system-messages">
@if (Session::has('success'))
	<div class="alert alert-success" role="alert">
		<?php
		$err = Session::get('success');

		if (is_array($err)):
			foreach ($err as $i => $er):
				$err[$i] = e($er);
				echo implode('<br />', $err);
			endforeach;
		else:
			echo e($err);
		endif;
		?>
	</div>
@endif
@if (Session::has('error'))
	<div class="alert alert-danger" role="alert">
		<?php
		$err = Session::get('error');

		if (is_array($err)):
			foreach ($err as $i => $er):
				$err[$i] = e($er);
				echo implode('<br />', $err);
			endforeach;
		else:
			echo e($err);
		endif;
		?>
	</div>
@endif
@if (Session::has('warning'))
	<div class="alert alert-warning" role="alert">
		<?php
		$err = Session::get('warning');

		if (is_array($err)):
			foreach ($err as $i => $er):
				$err[$i] = e($er);
				echo implode('<br />', $err);
			endforeach;
		else:
			echo e($err);
		endif;
		?>
	</div>
@endif
</div>
