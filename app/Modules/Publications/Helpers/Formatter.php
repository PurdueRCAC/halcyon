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
	 * @var  array
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
	 * @var  array
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
	 * @param  string  Template keys that will be used to format the citation
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
	 * @return  string  Template string that is being used to format citations
	 */
	public static function getTemplateKeys()
	{
		return self::$template_keys;
	}

	/**
	 * Function to format citation based on template
	 *
	 * @param   object   $citation       Citation object
	 * @param   string   $highlight      String that we want to highlight
	 * @param   boolean  $include_coins  Include COINs?
	 * @param   object   $config         Registry
	 * @param   boolean  $coins_only     Only output COINs?
	 * @return  string   Formatted citation
	 */
	public static function format(Publication $publication, $template = null)
	{
		$template = __NAMESPACE__ . '\\Formats\\' . self::getDefaultFormat();

		return $template::format($publication);

		if ($template)
		{
			$template = Format::query()
				->where('alias', '=', $template)
				->get()
				->first();
		}

		if (!$template)
		{
			$template = self::getDefaultFormat();
		}

		//get hub specific details
		$hub_name = config('app.name');
		$hub_url  = rtrim(url('/'), '/');

		$type = $publication->type->alias;

		$c_type = 'journal';
		switch (strtolower($type))
		{
			case 'book':
			case 'inbook':
			case 'conference':
			case 'proceedings':
			case 'inproceedings':
				$c_type = "book";
				break;
			case 'journal':
			case 'article':
			case 'journal article';
				$c_type = "journal";
				break;
			default:
			break;
		}

		//var to hold COinS data
		$coins_data = array(
			"ctx_ver=Z39.88-2004",
			"rft_val_fmt=info:ofi/fmt:kev:mtx:{$c_type}",
			"rfr_id=info:sid/{$hub_url}:{$hub_name}"
		);

		//array to hold replace vals
		$replace_values = array();

		//get the template keys
		$template_keys = self::getTemplateKeys();

		foreach ($template_keys as $k => $v)
		{
			if (!self::keyExistsOrIsNotEmpty($k, $publication))
			{
				$replace_values[$v] = '';
			}
			else
			{
				$replace_values[$v] = $publication->$k;

				// Add to COINS data if we can but not authors as that will get processed below
				if (in_array($k, array_keys(self::$coins_keys)) && $k != 'author')
				{
					switch ($k)
					{
						case 'title':
							break;
						case 'doi':
							$coins_data[] = self::$coins_keys[$k] . $publication->$k;
							break;
						case 'url':
							$coins_data[] = self::$coins_keys[$k] . '=' . htmlentities($publication->$k);
							break;
						case 'journaltitle':
							$jt = html_entity_decode($publication->$k);
							$jt = (!preg_match('!\S!u', $jt)) ? mb_convert_encoding($jt, 'UTF-8', 'ISO-8859-1') : $jt;
							$coins_data[] = self::$coins_keys[$k] . '=' . $jt;
							break;
						default:
							$coins_data[] = self::$coins_keys[$k] . '=' . $publication->$k;
					}
				}

				if ($k == 'doi' && $publication->$k)
				{
					$doi = str_replace('https://doi.org/', '',  $publication->$k);
					$doi = str_replace('https://dx.doi.org/', '',$doi);
					$doi = str_replace('http://doi.org/', '', $doi);
					$doi = str_replace('http://dx.doi.org/', '', $doi);

					$replace_values[$v] = '<a rel="external" href="https://doi.org/' .  $doi . '">' . $doi . '</a>';
				}

				if ($k == 'author')
				{
					$a = array();

					$auth = html_entity_decode($publication->$k);
					$auth = (!preg_match('!\S!u', $auth)) ? mb_convert_encoding($auth, 'UTF-8', 'ISO-8859-1') : $auth;

					$author_string = $auth;
					$authors = explode(';', $author_string);

					foreach ($authors as $author)
					{
						$a[] = $author;

						//add author coins
						$coins_data[] = 'rft.au=' . trim(preg_replace('/\{\{\d+\}\}/', '', trim($author)));
					}

					$replace_values[$v] = implode(', ', $a);
				}

				if ($k == 'title')
				{
					//$url_format = $config->get("citation_url", "url");
					//$custom_url = $config->get("citation_custom_url", '');

					$url = $publication->url;
					/*if ($url_format == 'custom' && $custom_url != '')
					{
						//parse custom url to make sure we are not using any vars
						preg_match_all('/\{(\w+)\}/', $custom_url, $matches, PREG_SET_ORDER);
						if ($matches)
						{
							foreach ($matches as $match)
							{
								$field = strtolower($match[1]);
								$replace = $match[0];
								$replaceWith = '';
								if (property_exists($citation, $field))
								{
									if (strstr($publication->$field, 'http'))
									{
										$custom_url = $publication->$field;
									}
									else
									{
										$replaceWith = urlencode($publication->$field);
										$custom_url = str_replace($replace, $replaceWith, $custom_url);
									}
								}
							}
							//set the citation url to be the new custom url parsed
							$url  = $custom_url;
						}
					}*/

					//prepare url
					if (strstr($url, "\r\n"))
					{
						$url = array_filter(array_values(explode("\r\n", $url)));
						$url = $url[0];
					}
					elseif (strstr($url, " "))
					{
						$url = array_filter(array_values(explode(' ', $url)));
						$url = $url[0];
					}

					$t = html_entity_decode($publication->$k);
					$t = (!preg_match('!\S!u', $t)) ? mb_convert_encoding($t, 'UTF-8', 'ISO-8859-1') : $t;

					$title = ($url != '' && preg_match('/http:|https:/', $url))
							? '<a rel="external" class="publication-title" href="' . $url . '">' . $t . '</a>'
							: '<span class="publication-title">' . $t . '</span>';


					//send back title to replace title placeholder ({TITLE})
					$replace_values[$v] = '"' . $title . '"';

					//add title to coin data but fixing bad chars first
					$coins_data[] = 'rft.atitle=' . $t;
				}

				if ($k == 'pages')
				{
					$replace_values[$v] = 'pg: ' . $publication->$k;
				}

				if ($k == 'version')
				{
					$replace_values[$v] = "(Version " . $publication->$k . ")";
				}
			}
		}

		// Add more to coins
		$tmpl = $template->template;
		$cite = strtr($tmpl, $replace_values);

		// Strip empty tags
		$pattern = "/<[^\/>]*>([\s]?)*<\/[^>]*>/";
		$cite = preg_replace($pattern, '', $cite);

		// Reformat dates
		$pattern = "/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/";
		$cite = preg_replace($pattern, "$2-$3-$1", $cite);

		// Reduce multiple spaces to one
		$pattern = "/\s/s";
		$cite = preg_replace($pattern, ' ', $cite);

		// Strip empty punctuation inside
		$b = array(
			"''" => '',
			'""' => '',
			'()' => '',
			'{}' => '',
			'[]' => '',
			'??' => '',
			'!!' => '',
			'..' => '.',
			',,' => ',',
			' ,' => '',
			' .' => '',
			',.' => '.',
			'","'=> '',
			'doi:.'=>'',
			'(DOI:).'=>''
		);

		foreach ($b as $k => $i)
		{
			$cite = str_replace($k, $i, $cite);
		}

		// Strip empty punctuation from the start
		$c = array(
			"' ",
			'" ',
			'(',
			') ',
			', ',
			'. ',
			'? ',
			'! ',
			': ',
			'; '
		);

		foreach ($c as $k)
		{
			if (substr($cite, 0, 2) == $k)
			{
				$cite = substr($cite, 2);
			}
		}

		// Remove trailing commas
		$cite = trim($cite);
		$cite = trim($cite, ',');

		// Percent encode chars
		$chars      = array('%', ' ', '/', ':', '"', '\'', '&amp;');
		$replace    = array("%20", "%20", "%2F", "%3A", "%22", "%27", "%26");
		$coins_data = str_replace($chars, $replace, implode('&', $coins_data));

		$cite = preg_replace('/, :/', ':', $cite);

		if ($include_coins)
		{
			$cite .= '<span class="Z3988" title="' . $coins_data . '"></span>';
		}

		return $cite;
	}

	/**
	 * Get the default formats (APA, IEEE, etc)
	 *
	 * @return  object
	 */
	public function getDefaultFormat()
	{
		return config('module.publications.format', 'IEEE');

		if (is_null(self::$template))
		{
			self::$template = Format::query()
				->where('is_default', '=', 1)
				->get()
				->first();
		}

		return self::$template;
	}

	/**
	 * Encode ampersands
	 *
	 * @param   string  $url  URL to encode
	 * @return  string
	 */
	/*public static function cleanUrl($url)
	{
		$url = stripslashes($url);
		$url = str_replace('&amp;', '&', $url);
		$url = str_replace('&', '&amp;', $url);

		return $url;
	}*/

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

	/**
	 * Ensure correct punctuation
	 *
	 * @param   string  $html   String to check punctuation on
	 * @param   string  $punct  Punctuation to insert
	 * @return  string
	 */
	/*public static function grammarCheck($html, $punct=',')
	{
		if (substr($html, -1) == '"')
		{
			$html = substr($html, 0, strlen($html)-1) . $punct . '"';
		}
		else
		{
			$html .= $punct;
		}
		return $html;
	}*/

	/**
	 * Formatting Resources
	 *
	 * @param   object  &$row       Record to format
	 * @param   string  $link       Parameter description (if any) ...
	 * @param   string  $highlight  String to highlight
	 * @return  string
	 */
	/*public static function formatReference(&$row, $link='none', $highlight='')
	{
		$html = "\t" . '<p>';
		if (self::keyExistsOrIsNotEmpty('author', $row))
		{
			$auths = explode(';', $row->author);
			$a = array();
			foreach ($auths as $auth)
			{
				preg_match('/{{(.*?)}}/s', $auth, $matches);
				if (isset($matches[0]) && $matches[0]!='')
				{
					$matches[0] = preg_replace('/{{(.*?)}}/s', '\\1', $matches[0]);
					$aid = 0;
					if (is_numeric($matches[0]))
					{
						$aid = $matches[0];
					}
					else
					{
						$zuser = \User::getInstance(trim($matches[0]));
						if (is_object($zuser))
						{
							$aid = $zuser->get('id');
						}
					}
					$auth = preg_replace('/{{(.*?)}}/s', '', $auth);
					if ($aid)
					{
						$a[] = '<a href="' . \Route::url('index.php?option=com_members&id=' . $aid) . '">' . trim($auth) . '</a>';
					}
					else
					{
						$a[] = trim($auth);
					}
				}
				else
				{
					$a[] = trim($auth);
				}
			}
			$row->author = implode('; ', $a);

			$html .= stripslashes($row->author);
		}
		elseif (self::keyExistsOrIsNotEmpty('editor', $row))
		{
			$html .= stripslashes($row->editor);
		}

		if (self::keyExistsOrIsNotEmpty('year', $row))
		{
			$html .= ' (' . $row->year . ')';
		}

		if (self::keyExistsOrIsNotEmpty('title', $row))
		{
			if (!$row->url)
			{
				$html .= ', "' . stripslashes($row->title);
			}
			else
			{
				$html .= ', "<a href="' . self::cleanUrl($row->url) . '">' . Str::highlight(stripslashes($row->title), $highlight) . '</a>';
			}
		}
		if (self::keyExistsOrIsNotEmpty('journal', $row)
		 || self::keyExistsOrIsNotEmpty('edition', $row)
		 || self::keyExistsOrIsNotEmpty('booktitle', $row))
		{
			$html .= ',';
		}
		$html .= '"';
		if (self::keyExistsOrIsNotEmpty('journal', $row))
		{
			$html .= ' <i>' . Str::highlight(stripslashes($row->journal), $highlight) . '</i>';
		}
		elseif (self::keyExistsOrIsNotEmpty('booktitle', $row))
		{
			$html .= ' <i>' . stripslashes($row->booktitle) . '</i>';
		}
		if ($row->type)
		{
			switch ($row->type)
			{
				case 'phdthesis':
					$html .= ' (' . \Lang::txt('PhD Thesis') . ')';
					break;
				case 'mastersthesis':
					$html .= ' (' . \Lang::txt('Masters Thesis') . ')';
					break;
				default:
					break;
			}
		}
		if (self::keyExistsOrIsNotEmpty('edition', $row))
		{
			$html  = self::grammarCheck($html, ',');
			$html .= ' ' . $row->edition;
		}
		if (self::keyExistsOrIsNotEmpty('chapter', $row))
		{
			$html  = self::grammarCheck($html, ',');
			$html .= ' ' . stripslashes($row->chapter);
		}
		if (self::keyExistsOrIsNotEmpty('series', $row))
		{
			$html  = self::grammarCheck($html, ',');
			$html .= ' ' . stripslashes($row->series);
		}
		if (self::keyExistsOrIsNotEmpty('publisher', $row))
		{
			$html  = self::grammarCheck($html, ',');
			$html .= ' ' . stripslashes($row->publisher);
		}
		if (self::keyExistsOrIsNotEmpty('address', $row))
		{
			$html  = self::grammarCheck($html, ',');
			$html .= ' ' . stripslashes($row->address);
		}
		if (self::keyExistsOrIsNotEmpty('volume', $row))
		{
			$html  = self::grammarCheck($html, ',');
			$html .= ' <b>' . $row->volume . '</b>';
		}
		if (self::keyExistsOrIsNotEmpty('number', $row))
		{
			$html  = self::grammarCheck($html, ',');
			$html .= ' <b>' . $row->number . '</b>';
		}
		if (self::keyExistsOrIsNotEmpty('pages', $row))
		{
			$html .= ': pg. ' . $row->pages;
		}
		if (self::keyExistsOrIsNotEmpty('organization', $row))
		{
			$html  = self::grammarCheck($html, ',');
			$html .= ' ' . stripslashes($row->organization);
		}
		if (self::keyExistsOrIsNotEmpty('institution', $row))
		{
			$html  = self::grammarCheck($html, ',');
			$html .= ' ' . stripslashes($row->institution);
		}
		if (self::keyExistsOrIsNotEmpty('school', $row))
		{
			$html  = self::grammarCheck($html, ',');
			$html .= ' ' . stripslashes($row->school);
		}
		if (self::keyExistsOrIsNotEmpty('location', $row))
		{
			$html  = self::grammarCheck($html, ',');
			$html .= ' ' . stripslashes($row->location);
		}
		if (self::keyExistsOrIsNotEmpty('month', $row))
		{
			$html  = self::grammarCheck($html, ',');
			$html .= ' ' . $row->month;
		}
		if (self::keyExistsOrIsNotEmpty('isbn', $row))
		{
			$html  = self::grammarCheck($html, '.');
			$html .= ' ' . $row->isbn;
		}
		if (self::keyExistsOrIsNotEmpty('doi', $row))
		{
			$row->doi = str_replace('https://doi.org/', '', $row->doi);
			$row->doi = str_replace('https://dx.doi.org/', '', $row->doi);
			$row->doi = str_replace('http://doi.org/', '', $row->doi);
			$row->doi = str_replace('http://dx.doi.org/', '', $row->doi);

			$html  = self::grammarCheck($html, '.');
			$html .= ' (' . \Lang::txt('DOI') . ': <a rel="external" href="https://doi.org/' . $row->doi . '">' . $row->doi . '</a>)';
		}
		$html  = self::grammarCheck($html, '.');
		$html .= '</p>' . "\n";

		return $html;
	}*/
}
