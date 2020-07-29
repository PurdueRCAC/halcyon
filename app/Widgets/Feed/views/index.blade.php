<?php
/**
 * @package    halcyon
 * @copyright  Copyright 2020 Purdue University.
 * @license    http://opensource.org/licenses/MIT MIT
 */

if ($feed != false)
{
	//image handling
	$iUrl   = isset($feed->image->url)   ? $feed->image->url   : null;
	$iTitle = isset($feed->image->title) ? $feed->image->title : null;
	$actualItems = count($feed->items);
	$setItems    = $params->get('rssitems', 5);

	if ($setItems > $actualItems)
	{
		$totalItems = $actualItems;
	}
	else
	{
		$totalItems = $setItems;
	}
	?>
	<div class="feed{{ $clss }} feed-{{ $rssrtl ? 'rtl' : 'ltr' }}">
		@if (!is_null($feed->title) && $params->get('rsstitle', 1))
			<h4>
				<a href="{{ str_replace('&', '&amp', $feed->link) }}" rel="nofollow external">
					{{ $feed->title }}
				</a>
			</h4>
		@endif

		@if ($params->get('rssdesc', 1))
			{{ $feed->description }}
		@endif

		@if ($params->get('rssimage', 1) && $iUrl)
			<img src="{{ $iUrl }}" alt="{{ $iTitle }}" />
		@endif

		<ul class="newsfeed {{ $params->get('moduleclass_sfx') }}">
			<?php
			$words = $params->def('word_count', 0);
			for ($j = 0; $j < $totalItems; $j ++)
			{
				$currItem = & $feed->items[$j];
				// item title
				?>
				<li class="newsfeed-item">
					<?php
					if (!is_null($currItem->get_link()))
					{
						if (!is_null($feed->title) && $params->get('rsstitle', 1))
						{
							echo '<h5 class="feed-link">';
						}
						else
						{
							echo '<h4 class="feed-link">';
						}
						?>
						<a href="<?php echo $currItem->get_link(); ?>" rel="nofollow external">
							<?php echo $currItem->get_title(); ?>
						</a>
						<?php
						if (!is_null($feed->title) && $params->get('rsstitle', 1))
						{
							echo '</h5>';
						}
						else
						{
							echo '</h4>';
						}
					}

					// item description
					if ($params->get('rssitemdesc', 1))
					{
						// item description
						$text = $currItem->get_description();
						$text = str_replace('&apos;', "'", $text);
						$text = strip_tags($text);
						// word limit check
						if ($words)
						{
							$texts = explode(' ', $text);
							$count = count($texts);
							if ($count > $words)
							{
								$text = '';
								for ($i = 0; $i < $words; $i ++)
								{
									$text .= ' '.$texts[$i];
								}
								$text .= '...';
							}
						}
						?>
						<p><?php echo $text; ?></p>
						<?php
					}
					?>
				</li>
				<?php
			}
			?>
		</ul>
	</div>
	<?php
}
