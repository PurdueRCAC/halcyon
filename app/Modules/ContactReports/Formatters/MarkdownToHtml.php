<?php
namespace App\Modules\ContactReports\Formatters;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use Closure;

/**
 * Convert MarkDown to HTML
 */
class MarkdownToHtml
{
	/**
	 * Handle content
	 *
	 * @param  array $data
	 * @param  Closure $next
	 * @return array
	 */
	public function handle(array $data, Closure $next): array
	{
		$converter = new CommonMarkConverter([
			'html_input' => 'allow',
		]);
		$converter->getEnvironment()->addExtension(new TableExtension());
		$converter->getEnvironment()->addExtension(new StrikethroughExtension());
		$converter->getEnvironment()->addExtension(new AutolinkExtension());

		$data['content'] = (string) $converter->convertToHtml($data['content']);

		return $next($data);
	}
}
