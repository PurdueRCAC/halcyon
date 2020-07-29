<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// Note. It is important to remove spaces between elements.
?>
<ul class="menu<?php echo $class_sfx ? ' ' . $class_sfx : ''; ?>"<?php
	$tag = '';
	if ($params->get('tag_id') != null)
	{
		$tag = $params->get('tag_id').'';
		echo ' id="' . $tag . '"';
	}
?>>
	<?php
	$current = trim(request()->path(), '/');

	foreach ($list as $i => $item):
		$class = 'item-' . $item->id;
		if ($item->id == $active_id)
		{
			$class .= ' current';
		}

		//if (in_array($item->id, $path))
		if (trim($item->link, '/') == $current)
		{
			$class .= ' active';
		}
		/*elseif ($item->type == 'alias')
		{
			$aliasToId = $item->params->get('aliasoptions');
			if (count($path) > 0 && $aliasToId == $path[count($path)-1])
			{
				$class .= ' active';
			}
			elseif (in_array($aliasToId, $path))
			{
				$class .= ' alias-parent-active';
			}
		}*/

		if ($item->deeper)
		{
			$class .= ' deeper';
		}

		if ($item->parent)
		{
			$class .= ' dropdown parent';
		}

		if (!empty($class))
		{
			$class = ' class="' . trim($class) . '"';
		}

		if (!isset($item->data))
		{
			$item->data = array();
		}

		echo '<li' . $class . '>';

		// Render the menu item.
		switch ($item->type) :
			case 'separator':
				// Note. It is important to remove spaces between elements.
				$title = $item->anchor_title ? ' title="' . $item->anchor_title . '" ' : '';
				if ($item->menu_image)
				{
					$item->params->get('menu_text', 1) ?
						$linktype = '<img src="' . $item->menu_image . '" alt="' . $item->title . '" /><span class="image-title">' . $item->title . '</span> ' :
						$linktype = '<img src="' . $item->menu_image . '" alt="' . $item->title . '" />';
				}
				else
				{
					$linktype = $item->title;
				}
				?><div class="separator"<?php echo $title; ?>><?php //echo $linktype; ?></div><?php
			break;
			case 'url':
				if ($item->parent):
					$item->anchor_css .= ' dropdown-toggle';
					$item->data['toggle'] = 'dropdown';
				endif;

				// Note. It is important to remove spaces between elements.
				$class = $item->anchor_css   ? 'class="' . $item->anchor_css . '" '   : '';
				$title = $item->anchor_title ? 'title="' . $item->anchor_title . '" ' : '';
				if ($item->menu_image)
				{
					$item->params->get('menu_text', 1) ?
						$linktype = '<img src="' . $item->menu_image . '" alt="' . $item->title . '" /><span class="image-title">' . $item->title . '</span> ' :
						$linktype = '<img src="' . $item->menu_image . '" alt="' . $item->title . '" />';
				}
				else
				{
					$linktype = $item->title;
				}
				if ($item->parent):
					$linktype .= '<span class="caret"></span>';
				endif;
				$flink = $item->flink;
				//$flink = \Hubzero\Utility\Str::ampReplace(htmlspecialchars($flink));

				switch ($item->target) :
					default:
					case 0:
						?><a <?php echo $class; ?>href="<?php echo $flink; ?>" <?php echo $title; ?>><?php echo $linktype; ?></a><?php
						break;
					case 1:
						// _blank
						?><a <?php echo $class; ?>href="<?php echo $flink; ?>" rel="noopener" target="_blank" <?php echo $title; ?>><?php echo $linktype; ?></a><?php
						break;
					case 2:
						// window.open
						$options = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,'.$params->get('window_open');
							?><a <?php echo $class; ?>href="<?php echo $flink; ?>" onclick="window.open(this.href,'targetWindow','<?php echo $options;?>');return false;" <?php echo $title; ?>><?php echo $linktype; ?></a><?php
						break;
				endswitch;
			break;
			case 'module':
				if ($item->parent):
					$item->anchor_css .= ' dropdown-toggle';
					$item->data['toggle'] = 'dropdown';
				endif;

				$atts = array(
					'href="' . $item->flink . '"'
				);

				if ($item->anchor_css):
					$atts[] = 'class="' . trim($item->anchor_css) . '"';
				endif;

				if ($item->anchor_title):
					$atts[] = 'title="' . $item->anchor_title . '"';
				endif;

				foreach ($item->data as $k => $v):
					$atts[] = 'data-' . $k . '="' . $v . '"';
				endforeach;

				// Note. It is important to remove spaces between elements.
				//$class = $item->anchor_css   ? 'class="' . $item->anchor_css . '" '   : '';
				//$title = $item->anchor_title ? 'title="' . $item->anchor_title . '" ' : '';
				if ($item->menu_image)
				{
					$item->params->get('menu_text', 1) ?
						$linktype = '<img src="' . $item->menu_image . '" alt="' . $item->title . '" /><span class="image-title">' . $item->title . '</span> ' :
						$linktype = '<img src="' . $item->menu_image . '" alt="' . $item->title . '" />';
				}
				else
				{
					$linktype = $item->title;
				}

				if ($item->parent):
					$linktype .= '<span class="caret"></span>';
				endif;

				switch ($item->target) :
					default:
					case 0:
						?><a <?php echo implode(' ', $atts); ?>><?php echo $linktype; ?></a><?php
						break;
					case 1:
						// _blank
						?><a <?php echo implode(' ', $atts); ?> rel="noopener" target="_blank"><?php echo $linktype; ?></a><?php
						break;
					case 2:
					// window.open
						?><a <?php echo implode(' ', $atts); ?> onclick="window.open(this.href,'targetWindow','toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes');return false;"><?php echo $linktype; ?></a><?php
						break;
				endswitch;
			break;

			default:
				// Note. It is important to remove spaces between elements.
				$class = $item->anchor_css   ? 'class="' . $item->anchor_css . '" '   : '';
				$title = $item->anchor_title ? 'title="' . $item->anchor_title . '" ' : '';
				if ($item->menu_image)
				{
					$item->params->get('menu_text', 1) ?
						$linktype = '<img src="' . $item->menu_image . '" alt="' . $item->title . '" /><span class="image-title">' . $item->title . '</span> ' :
						$linktype = '<img src="' . $item->menu_image . '" alt="' . $item->title . '" />';
				}
				else
				{
					$linktype = $item->title;
				}

				if ($item->parent):
					$linktype .= '<span class="caret"></span>';
				endif;
				$flink = $item->flink;
				//$flink = \Hubzero\Utility\Str::ampReplace(htmlspecialchars($flink));

				switch ($item->target) :
					default:
					case 0:
						?><a <?php echo $class; ?>href="<?php echo $flink; ?>" <?php echo $title; ?>><?php echo $linktype; ?></a><?php
						break;
					case 1:
						// _blank
						?><a <?php echo $class; ?>href="<?php echo $flink; ?>" rel="noopener" target="_blank" <?php echo $title; ?>><?php echo $linktype; ?></a><?php
						break;
					case 2:
						// window.open
						$options = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,'.$params->get('window_open');
							?><a <?php echo $class; ?>href="<?php echo $flink; ?>" onclick="window.open(this.href,'targetWindow','<?php echo $options;?>');return false;" <?php echo $title; ?>><?php echo $linktype; ?></a><?php
						break;
				endswitch;
			break;
		endswitch;

		// The next item is deeper.
		if ($item->deeper)
		{
			echo '<ul class="dropdown-menu">';
		}
		// The next item is shallower.
		elseif ($item->shallower)
		{
			echo '</li>';
			echo str_repeat('</ul></li>', $item->level_diff);
		}
		// The next item is on the same level.
		else
		{
			echo '</li>';
		}
	endforeach;
	?>
</ul>
