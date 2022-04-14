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
	 * @param  object $row Record to format
	 * @return string
	 */
	public function format(Publication $row)
	{
		//var to hold document conetnt
		$doc = '';

		$type = $row->type->name;

		//set the type
		$doc .= "%0 {$type}" . "\r\n";

		if ($row->booktitle)
		{
			$bt = html_entity_decode($row->booktitle);
			$bt = (!preg_match('!\S!u', $bt)) ? utf8_encode($bt) : $bt;
			$doc .= "%B " . $bt . "\r\n";
		}

		if ($row->journal)
		{
			$j = html_entity_decode($row->journal);
			$j = (!preg_match('!\S!u', $j)) ? utf8_encode($j) : $j;
			$doc .= "%J " . $j . "\r\n";
		}

		if ($row->published_at)
		{
			$doc .= "%D " . $row->published_at->format('Y') . "\r\n";
		}

		if ($row->title)
		{
			$t = html_entity_decode($row->title);
			$t = (!preg_match('!\S!u', $t)) ? utf8_encode($t) : $t;
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
			$editor = (!preg_match('!\S!u', $editor)) ? utf8_encode($editor) : $editor;

			$author_array = explode(';', stripslashes($editor));
			foreach ($author_array as $auth)
			{
				$doc .= "%E " . trim($auth) . "\r\n";
			}
		}

		if ($row->publisher)
		{
			$p = html_entity_decode($row->publisher);
			$p = (!preg_match('!\S!u', $p)) ? utf8_encode($p) : $p;
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
			$n = (!preg_match('!\S!u', $n)) ? utf8_encode($n) : $n;
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
			$k = (!preg_match('!\S!u', $k)) ? utf8_encode($k) : $k;
			$doc .= "%K " . $k . "\r\n";
		}

		if ($row->research_notes)
		{
			$rn = html_entity_decode($row->research_notes);
			$rn = (!preg_match('!\S!u', $rn)) ? utf8_encode($rn) : $rn;
			$doc .= "%< " . $rn . "\r\n";
		}*/

		if ($row->abstract)
		{
			$a = html_entity_decode($row->abstract);
			$a = (!preg_match('!\S!u', $a)) ? utf8_encode($a) : $a;
			$doc .= "%X " . $a . "\r\n";
		}
		/*if ($row->label)
		{
			$l = html_entity_decode($row->label);
			$l = (!preg_match('!\S!u', $l)) ? utf8_encode($l) : $l;
			$doc .= "%F " . $label . "\r\n";
		}
		if ($row->language)
		{
			$lan = html_entity_decode($row->language);
			$lan = (!preg_match('!\S!u', $lan)) ? utf8_encode($lan) : $lan;
			$doc .= "%G " . $lan . "\r\n";
		}
		if ($row->author_address)
		{
			$aa = html_entity_decode($row->author_address);
			$aa = (!preg_match('!\S!u', $aa)) ? utf8_encode($aa) : $aa;
			$doc .= "%+ " . $aa . "\r\n";
		}
		if ($row->accession_number)
		{
			$an = html_entity_decode($row->accession_number);
			$an = (!preg_match('!\S!u', $an)) ? utf8_encode($an) : $an;
			$doc .= "%M " . trim($an) . "\r\n";
		}
		if ($row->call_number)
		{
			$doc .= "%L " . trim($row->call_number) . "\r\n";
		}
		if ($row->short_title)
		{
			$st = html_entity_decode($row->short_title);
			$st = (!preg_match('!\S!u', $st)) ? utf8_encode($st) : $st;
			$doc .= "%! " . htmlspecialchars_decode(trim($st)) . "\r\n";
		}*/

		$doc .= "\r\n";

		return $doc;
	}
}
