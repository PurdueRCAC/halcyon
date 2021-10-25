<?php
/**
 * @package  Submenu widget
 */

$hide = Illuminate\Support\Facades\Request::input('hidemainmenu');
?>
<ul id="submenu">
	<?php foreach ($list as $item): ?>
		<li>
			<?php
			if ($hide):
				if (isset ($item[2]) && $item[2] == 1):
					?><span class="nolink active"><?php echo e($item[0]); ?></span><?php
				else:
					?><span class="nolink"><?php echo e($item[0]); ?></span><?php
				endif;
			else:
				if (strlen($item[1])):
					if (isset ($item[2]) && $item[2] == 1):
						?><a class="active" href="<?php echo App\Halcyon\Utility\Str::ampReplace($item[1]); ?>"><?php echo e($item[0]); ?></a><?php
					else:
						?><a href="<?php echo App\Halcyon\Utility\Str::ampReplace($item[1]); ?>"><?php echo e($item[0]); ?></a><?php
					endif;
				else:
					?><?php echo e($item[0]); ?><?php
				endif;
			endif;
			?>
		</li>
	<?php endforeach; ?>
</ul>
<?php
$list = array();

if (app()->has('subsubmenu')):
	$list = app('subsubmenu')->getItems();
endif;

if (is_array($list) && count($list)):
	?>
	<nav role="navigation" class="sub sub-navigation">
		<ul>
			<?php foreach ($list as $item): ?>
				<li>
					<?php
					if ($hide):
						if (isset ($item[2]) && $item[2] == 1):
							?><span class="nolink active"><?php echo e($item[0]); ?></span><?php
						else:
							?><span class="nolink"><?php echo e($item[0]); ?></span><?php
						endif;
					else:
						if (strlen($item[1])):
							if (isset ($item[2]) && $item[2] == 1):
								?><a class="active" href="<?php echo App\Halcyon\Utility\Str::ampReplace($item[1]); ?>"><?php echo e($item[0]); ?></a><?php
							else:
								?><a href="<?php echo App\Halcyon\Utility\Str::ampReplace($item[1]); ?>"><?php echo e($item[0]); ?></a><?php
							endif;
						else:
							?><?php echo e($item[0]); ?><?php
						endif;
					endif;
					?>
				</li>
			<?php endforeach; ?>
		</ul>
	</nav>
	<?php
endif;
