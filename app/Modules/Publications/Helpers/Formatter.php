<?php

namespace App\Modules\Publications\Helpers;

use App\Modules\Publications\Models\Publication;
use App\Modules\Publications\Models\Format;

/**
 * Citations helper class for formatting results
 */
class Formatter
{
	/**
	 * Replacement values in format templates
	 *
	 * @var  array<string,string>
	 */
	protected static $template_keys = array(
		'type' => '{TYPE}',
		'cite' => '{CITE KEY}',
		'ref_type' => '{REF TYPE}',
		'date_submit' => '{DATE SUBMITTED}',
		'date_accept' => '{DATE ACCEPTED}',
		'date_publish' => '{DATE PUBLISHED}',
		'author' => '{AUTHORS}',
		'editor' => '{EDITORS}',
		'title' => '{TITLE/CHAPTER}',
		'booktitle' => '{BOOK TITLE}',
		'chapter' => '{CHAPTER}',
		'journal' => '{JOURNAL}',
		'journaltitle' => '{JOURNAL TITLE}',
		'volume' => '{VOLUME}',
		'number' => '{ISSUE/NUMBER}',
		'pages' => '{PAGES}',
		'isbn' => '{ISBN/ISSN}',
		'issn' => '{ISSN}',
		'doi' => '{DOI}',
		'series' => '{SERIES}',
		'edition' => '{EDITION}',
		'school' => '{SCHOOL}',
		'publisher' => '{PUBLISHER}',
		'institution' => '{INSTITUTION}',
		'address' => '{ADDRESS}',
		'location' => '{LOCATION}',
		'howpublished' => '{HOW PUBLISHED}',
		'url' => '{URL}',
		'eprint' => '{E-PRINT}',
		'note' => '{TEXT SNIPPET/NOTES}',
		'organization' => '{ORGANIZATION}',
		'abstract' => '{ABSTRACT}',
		'year' => '{YEAR}',
		'month' => '{MONTH}',
		'search_string' => '{SECONDARY LINK}',
		'sec_cnt' => '{SECONDARY COUNT}',
		'version' => '{VERSION}',
	);

	/**
	 * Values used by COINs
	 *
	 * @var  array<string,string>
	 */
	protected static $coins_keys = array(
		'title'        => 'rft.atitle',
		'journaltitle' => 'rft.jtitle',
		'date_publish' => 'rft.date',
		'volume'       => 'rft.volume',
		'number'       => 'rft.issue',
		'pages'        => 'rft.pages',
		'issn'         => 'rft.issn',
		'isbn'         => 'rft.isbn',
		'type'         => 'rft.genre',
		'author'       => 'rft.au',
		'url'          => 'rft_id',
		'doi'          => 'rft_id=info:doi/',
		'author'       => 'rft.au'
	);

	/**
	 * Function to set the template keys the formatter will use
	 *
	 * @param  array $template_keys
	 * @return void
	 */
	public static function setTemplateKeys($template_keys)
	{
		if (!empty($template_keys))
		{
			self::$template_keys = $template_keys;
		}
	}

	/**
	 * Function to get the formatter template keys being used
	 *
	 * @return  array<string,string>
	 */
	public static function getTemplateKeys()
	{
		return self::$template_keys;
	}

	/**
	 * Function to format citation based on template
	 *
	 * @param   Publication $publication
	 * @param   string   $template
	 * @return  string   Formatted citation
	 */
	public static function format(Publication $publication, $template = null)
	{
		$template = __NAMESPACE__ . '\\Formats\\' . self::getDefaultFormat();

		return $template::format($publication);
	}

	/**
	 * Get the default formats (APA, IEEE, etc)
	 *
	 * @return  string
	 */
	public static function getDefaultFormat()
	{
		return config('module.publications.format', 'IEEE');
	}

	/**
	 * Check if a property of an object exist and is filled in
	 *
	 * @param   string   $key  Property name
	 * @param   object   $row  Object to look in
	 * @return  boolean  True if exists, false if not
	 */
	public static function keyExistsOrIsNotEmpty($key, $row)
	{
		if (!is_null($row->$key))
		{
			if ($row->$key != '' && $row->$key != '0' && $row->$key != '0000-00-00 00:00:00')
			{
				return true;
			}
		}

		return false;
	}
}
