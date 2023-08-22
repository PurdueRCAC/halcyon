<?php

namespace App\Halcyon\Html\Builder;

use Illuminate\Support\Fluent;

/**
 * Utility class to fire onContentPrepare for non-article based content.
 */
class Content
{
	/**
	 * Fire onContentPrepare for content that isn't part of an article.
	 *
	 * @param   string  $text     The content to be transformed.
	 * @param   Fluent|null   $params   The content params.
	 * @param   string  $context  The context of the content to be transformed.
	 * @return  string  The content after transformation.
	 */
	public static function prepare($text, $params = null, $context = 'text')
	{
		if ($params === null)
		{
			$params = new Fluent;
		}

		$article = new \stdClass;
		$article->text = $text;

		event('onContentPrepare', array($context, &$article, &$params, 0));

		return $article->text;
	}
}
