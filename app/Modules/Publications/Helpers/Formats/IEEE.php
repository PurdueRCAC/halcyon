<?php

namespace App\Modules\Publications\Helpers\Formats;

use App\Modules\Publications\Models\Publication;
use App\Modules\Publications\Helpers\Format;

/**
 * Publication IEEE format
 */
class IEEE implements Format
{
	/**
	 * Ensure encoded ampersands
	 *
	 * @param   string  $url
	 * @return  string
	 */
	public static function cleanUrl($url)
	{
		$url = str_replace('&amp;', '&', $url);
		$url = str_replace('&', '&amp;', $url);

		return $url;
	}

	/**
	 * Ensure correct punctuation
	 *
	 * @param   string  $html   String to check punctuation on
	 * @param   string  $punct  Punctuation to insert
	 * @return  string
	 */
	public static function grammarCheck($html, $punct=',')
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
	}

	/**
	 * Formatting Resources
	 *
	 * @param   Publication $publication
	 * @return  string
	 */
	public static function format(Publication $publication)
	{
		$html = '<p>';

		if ($publication->author)
		{
			/*$auths = explode(';', $publication->author);
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
			$publication->author = implode('; ', $a);*/

			$html .= $publication->author;
		}
		elseif ($publication->editor)
		{
			$html .= $publication->editor;
		}

		if ($publication->year)
		{
			$html .= ' (' . $publication->year . ')';
		}

		if ($publication->title)
		{
			$title = $publication->title;

			if ($publication->url)
			{
				$title = '<a href="' . self::cleanUrl($publication->url) . '">' . $title . '</a>';
			}

			$html .= ', "' . $title;
		}

		if ($publication->journal
		 || $publication->edition
		 || $publication->booktitle)
		{
			$html .= ',';
		}
		$html .= '"';

		if ($publication->journal)
		{
			$html .= ' <i>' . $publication->journal . '</i>';
		}
		elseif ($publication->booktitle)
		{
			$html .= ' <i>' . stripslashes($publication->booktitle) . '</i>';
		}

		if ($publication->type)
		{
			switch ($publication->type->alias)
			{
				case 'phdthesis':
				case 'mastersthesis':
					$html .= ' (' . $publication->type->name . ')';
					break;
				default:
					break;
			}
		}

		if ($publication->edition)
		{
			$html  = self::grammarCheck($html, ',');
			$html .= ' ' . $publication->edition;
		}
		if ($publication->chapter)
		{
			$html  = self::grammarCheck($html, ',');
			$html .= ' ' . stripslashes($publication->chapter);
		}
		if ($publication->series)
		{
			$html  = self::grammarCheck($html, ',');
			$html .= ' ' . stripslashes($publication->series);
		}
		if ($publication->publisher)
		{
			$html  = self::grammarCheck($html, ',');
			$html .= ' ' . stripslashes($publication->publisher);
		}
		if ($publication->address)
		{
			$html  = self::grammarCheck($html, ',');
			$html .= ' ' . stripslashes($publication->address);
		}
		if ($publication->issue)
		{
			$html  = self::grammarCheck($html, ',');
			$html .= ' iss. ' . $publication->issue;
		}
		if ($publication->volume)
		{
			$html  = self::grammarCheck($html, ',');
			$html .= ' vol. ' . $publication->volume;
		}
		if ($publication->number)
		{
			$html  = self::grammarCheck($html, ',');
			$html .= ' no. ' . $publication->number;
		}
		if ($publication->pages)
		{
			$html  = self::grammarCheck($html, ',');
			$html .= ' p';
			if (strstr($publication->pages, '-') || strstr($publication->pages, '–'))
			{
				$html .= 'p';
			}
			$html .= '. <b>' . $publication->pages . '</b>';
		}
		if ($publication->organization)
		{
			$html  = self::grammarCheck($html, ',');
			$html .= ' ' . stripslashes($publication->organization);
		}
		if ($publication->institution)
		{
			$html  = self::grammarCheck($html, ',');
			$html .= ' ' . stripslashes($publication->institution);
		}
		if ($publication->school)
		{
			$html  = self::grammarCheck($html, ',');
			$html .= ' ' . stripslashes($publication->school);
		}
		if ($publication->location)
		{
			$html  = self::grammarCheck($html, ',');
			$html .= ' ' . stripslashes($publication->location);
		}
		if ($publication->month)
		{
			$html  = self::grammarCheck($html, ',');
			$html .= ' ' . $publication->month;
		}
		if ($publication->isbn)
		{
			$html  = self::grammarCheck($html, '.');
			$html .= ' ' . $publication->isbn;
		}
		if ($publication->doi)
		{
			$publication->doi = str_replace('https://doi.org/', '', $publication->doi);
			$publication->doi = str_replace('https://dx.doi.org/', '', $publication->doi);
			$publication->doi = str_replace('http://doi.org/', '', $publication->doi);
			$publication->doi = str_replace('http://dx.doi.org/', '', $publication->doi);

			$html  = self::grammarCheck($html, '.');
			$html .= ' DOI: <a rel="external" href="https://doi.org/' . $publication->doi . '">' . $publication->doi . '</a>';
		}
		$html  = self::grammarCheck($html, '.');
		$html .= '</p>';

		return $html;
	}
}
