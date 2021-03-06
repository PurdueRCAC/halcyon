<?php
/**
 * @package  Breadcrumbs widget
 */
?>
<nav class="breadcrumbs{{ $class_sfx }} pathway{{ $class_sfx }}" aria-label="{{ trans('widget.breadcrumbs::breadcrumbs.widget name') }}">
	<div class="container<?php echo (app()->has('isAdmin') && app('isAdmin') ? '-fluid' : ''); ?>">
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12">
				<ol class="breadcrumb">
					@if ($params->get('showHere', 1))
						<li class="breadcrumb-item showHere">{{ trans('widget.breadcrumbs::breadcrumbs.here') }}</li>
					@endif
					<?php
					// Get rid of duplicated entries on trail including home page
					for ($i = 0; $i < $count; $i ++)
					{
						if ($i == 1
						 && !empty($list[$i]->link)
						 && !empty($list[$i-1]->link)
						 && $list[$i]->link == $list[$i-1]->link):
							unset($list[$i]);
						endif;
					}

					// Find last and penultimate items in breadcrumbs list
					end($list);
					$last_item_key = key($list);

					prev($list);
					$penult_item_key = key($list);

					// Make a link if not the last item in the breadcrumbs
					$show_last = $params->get('showLast', 1);

					// Generate the trail
					foreach ($list as $key => $item):
						if ($key != $last_item_key):
							// Render all but last item - along with separator
							if (!empty($item->link)):
								echo '<li class="breadcrumb-item"><a href="' . $item->link . '" class="pathway">' . Illuminate\Support\Str::limit($item->name, 30) . '</a></li>';
							else:
								echo '<li class="breadcrumb-item">' . Illuminate\Support\Str::limit($item->name, 30) . '</li>';
							endif;
						elseif ($show_last):
							// Render last item if reqd.
							echo '<li class="breadcrumb-item active" aria-current="page">' . $item->name . '</li>';
						endif;
					endforeach;
					?>
				</ol>
			</div>
		</div>
	</div>
</nav>
