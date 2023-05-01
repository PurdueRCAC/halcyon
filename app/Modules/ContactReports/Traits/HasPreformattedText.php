<?php
namespace App\Modules\ContactReports\Traits;

trait HasPreformattedText
{
	/**
	 * Code block replacements
	 *
	 * @var  array<string,array>
	 */
	protected $replacements = array(
		'preblocks'  => array(),
		'codeblocks' => array()
	);

	/**
	 * Strip pre and code blocks
	 *
	 * @param   string  $text
	 * @return  string
	 */
	protected function removePreformattedText(string $text): string
	{
		$text = $this->stripPre($text);
		$text = $this->stripCode($text);

		return $text;
	}

	/**
	 * Put back pre and code blocks
	 *
	 * @param   string  $text
	 * @return  string
	 */
	protected function putbackPreformattedText(string $text): string
	{
		$text = $this->replacePre($text);
		$text = $this->replaceCode($text);

		return $text;
	}

	/**
	 * Strip code blocks
	 *
	 * @param   string $text
	 * @return  string
	 */
	protected function stripCode(string $text): string
	{
		$text = preg_replace_callback(
			"/`(.*?)`/i",
			function ($match)
			{
				array_push($this->replacements['codeblocks'], $match[0]);

				return '{{CODE}}';
			},
			$text
		);

		return $text;
	}

	/**
	 * Strip pre blocks
	 *
	 * @param   string $text
	 * @return  string
	 */
	protected function stripPre(string $text): string
	{
		$text = preg_replace_callback(
			"/```(.*?)```/uis",
			function ($match)
			{
				array_push($this->replacements['preblocks'], $match[0]);

				return '{{PRE}}';
			},
			$text
		);

		return $text;
	}

	/**
	 * Replace code block
	 *
	 * @param   string $text
	 * @return  string
	 */
	protected function replaceCode(string $text): string
	{
		$text = preg_replace_callback(
			"/\{\{CODE\}\}/",
			function ($match)
			{
				return array_shift($this->replacements['codeblocks']);
			},
			$text
		);

		return $text;
	}

	/**
	 * Replace pre block
	 *
	 * @param   string $text
	 * @return  string
	 */
	protected function replacePre(string $text): string
	{
		$text = preg_replace_callback(
			"/\{\{PRE\}\}/",
			function ($match)
			{
				return array_shift($this->replacements['preblocks']);
			},
			$text
		);

		return $text;
	}
}
