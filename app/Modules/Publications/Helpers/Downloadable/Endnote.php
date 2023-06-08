<?php
namespace App\Modules\Publications\Helpers\Downloadable;

use App\Modules\Publications\Helpers\Downloadable;
use App\Modules\Publications\Models\Publication;

/**
 * Citations download class for Endnote format
 */
class Endnote extends Downloadable
{
	/**
	 * Mime type
	 *
	 * @var string
	 */
	protected $mimetype = 'application/x-endnote-refer';

	/**
	 * File extension
	 *
	 * @var string
	 */
	protected $extension = 'enw';

	/**
	 * Format the file
	 *
	 * @param  Publication $row Record to format
	 * @return string
	 */
	public function format(Publication $row): string
	{
		//var to hold document conetnt
		$doc = '';

		$type = $row->type ? $row->type->name : 'Article';

		//set the type
		$doc .= "%0 {$type}" . "\r\n";

		if ($row->booktitle)
		{
			$bt = html_entity_decode($row->booktitle);
			$bt = (!preg_match('!\S!u', $bt)) ? mb_convert_encoding($bt, 'UTF-8', 'ISO-8859-1') : $bt;
			$doc .= "%B " . $bt . "\r\n";
		}

		if ($row->journal)
		{
			$j = html_entity_decode($row->journal);
			$j = (!preg_match('!\S!u', $j)) ? mb_convert_encoding($j, 'UTF-8', 'ISO-8859-1') : $j;
			$doc .= "%J " . $j . "\r\n";
		}

		if ($row->published_at)
		{
			$doc .= "%D " . $row->published_at->format('Y') . "\r\n";
		}

		if ($row->title)
		{
			$t = html_entity_decode($row->title);
			$t = (!preg_match('!\S!u', $t)) ? mb_convert_encoding($t, 'UTF-8', 'ISO-8859-1') : $t;
			$doc .= "%T " . $t . "\r\n";
		}

		foreach ($row->authorList as $auth)
		{
			$doc .= "%A " . $auth['last'] . ', ' . $auth['first'] . "\r\n";
		}

		if ($row->address)
		{
			$doc .= "%C " . htmlspecialchars_decode(trim(stripslashes($row->address))) . "\r\n";
		}

		if ($row->editor)
		{
			$editor = html_entity_decode($row->editor);
			$editor = (!preg_match('!\S!u', $editor)) ? mb_convert_encoding($editor, 'UTF-8', 'ISO-8859-1') : $editor;

			$author_array = explode(';', stripslashes($editor));
			foreach ($author_array as $auth)
			{
				$doc .= "%E " . trim($auth) . "\r\n";
			}
		}

		if ($row->publisher)
		{
			$p = html_entity_decode($row->publisher);
			$p = (!preg_match('!\S!u', $p)) ? mb_convert_encoding($p, 'UTF-8', 'ISO-8859-1') : $p;
			$doc .= "%I " . $p . "\r\n";
		}

		if ($row->number)
		{
			$doc .= "%N " . trim($row->number) . "\r\n";
		}

		if ($row->pages)
		{
			$doc .= "%P " . trim($row->pages) . "\r\n";
		}

		if ($row->url)
		{
			$doc .= "%U " . trim($row->url) . "\r\n";
		}

		if ($row->volume)
		{
			$doc .= "%V " . trim($row->volume) . "\r\n";
		}

		if ($row->note)
		{
			$n = html_entity_decode($row->note);
			$n = (!preg_match('!\S!u', $n)) ? mb_convert_encoding($n, 'UTF-8', 'ISO-8859-1') : $n;
			$doc .= "%Z " . $n . "\r\n";
		}

		if ($row->edition)
		{
			$doc .= "%7 " . trim($row->edition) . "\r\n";
		}

		if ($row->month)
		{
			$doc .= "%8 " . trim($row->month) . "\r\n";
		}

		if ($row->isbn)
		{
			$doc .= "%@ " . trim($row->isbn) . "\r\n";
		}

		if ($row->doi)
		{
			$doc .= "%1 " . trim($row->doi) . "\r\n";
		}

		/*if ($row->keywords)
		{
			$k = html_entity_decode($row->keywords);
			$k = (!preg_match('!\S!u', $k)) ? mb_convert_encoding($k, 'UTF-8', 'ISO-8859-1') : $k;
			$doc .= "%K " . $k . "\r\n";
		}

		if ($row->research_notes)
		{
			$rn = html_entity_decode($row->research_notes);
			$rn = (!preg_match('!\S!u', $rn)) ? mb_convert_encoding($rn, 'UTF-8', 'ISO-8859-1') : $rn;
			$doc .= "%< " . $rn . "\r\n";
		}*/

		if ($row->abstract)
		{
			$a = html_entity_decode($row->abstract);
			$a = (!preg_match('!\S!u', $a)) ? mb_convert_encoding($a, 'UTF-8', 'ISO-8859-1') : $a;
			$doc .= "%X " . $a . "\r\n";
		}
		/*if ($row->label)
		{
			$l = html_entity_decode($row->label);
			$l = (!preg_match('!\S!u', $l)) ? mb_convert_encoding($l, 'UTF-8', 'ISO-8859-1') : $l;
			$doc .= "%F " . $label . "\r\n";
		}
		if ($row->language)
		{
			$lan = html_entity_decode($row->language);
			$lan = (!preg_match('!\S!u', $lan)) ? mb_convert_encoding($lan, 'UTF-8', 'ISO-8859-1') : $lan;
			$doc .= "%G " . $lan . "\r\n";
		}
		if ($row->author_address)
		{
			$aa = html_entity_decode($row->author_address);
			$aa = (!preg_match('!\S!u', $aa)) ? mb_convert_encoding($aa, 'UTF-8', 'ISO-8859-1') : $aa;
			$doc .= "%+ " . $aa . "\r\n";
		}
		if ($row->accession_number)
		{
			$an = html_entity_decode($row->accession_number);
			$an = (!preg_match('!\S!u', $an)) ? mb_convert_encoding($an, 'UTF-8', 'ISO-8859-1') : $an;
			$doc .= "%M " . trim($an) . "\r\n";
		}
		if ($row->call_number)
		{
			$doc .= "%L " . trim($row->call_number) . "\r\n";
		}
		if ($row->short_title)
		{
			$st = html_entity_decode($row->short_title);
			$st = (!preg_match('!\S!u', $st)) ? mb_convert_encoding($st, 'UTF-8', 'ISO-8859-1') : $st;
			$doc .= "%! " . htmlspecialchars_decode(trim($st)) . "\r\n";
		}*/

		$doc .= "\r\n";

		return $doc;
	}
}
