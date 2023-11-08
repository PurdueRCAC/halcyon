<?php
namespace App\Modules\Publications\Helpers\Downloadable;

use App\Modules\Publications\Helpers\Downloadable;
use App\Modules\Publications\Helpers\Parsers\BibTex as Parser;
use App\Modules\Publications\Models\Publication;

/**
 * Citations download class for BibText format
 */
class Bibtex extends Downloadable
{
	/**
	 * Mime type
	 *
	 * @var string
	 */
	protected $mimetype = 'application/x-bibtex';

	/**
	 * File extension
	 *
	 * @var string
	 */
	protected $extension = 'bib';

	/**
	 * Format the file
	 *
	 * @param  Publication $row Record to format
	 * @return string
	 */
	public function format(Publication $row): string
	{
		$addarray = array();

		if ($row->type)
		{
			$addarray['type'] = $row->type->name;
		}
		$addarray['title']    = $row->title;
		$addarray['address']  = $row->address;
		$addarray['author']   = $row->authorList;

		if (!empty($addarray['author']))
		{
			$author = $addarray['author'][0];
			$cite  = strtolower($author['last']);
			$cite .= $row->published_at->format('Y');
			$t = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($row->title));
			$cite .= (strlen($t) > 10 ? substr($t, 0, 10) : $t);

			$addarray['cite']         = $cite;
		}

		$addarray['booktitle']    = $row->booktitle;
		$addarray['chapter']      = $row->chapter;
		$addarray['edition']      = $row->edition;
		$addarray['editor']       = $row->editor;
		//$addarray['eprint']       = $row->eprint;
		//$addarray['howpublished'] = $row->howpublished;
		$addarray['institution']  = $row->institution;
		$addarray['journal']      = $row->journal;
		//$addarray['key']          = $row->key;
		//$addarray['location']     = $row->location;
		if ($row->published_at)
		{
			$addarray['year']         = $row->published_at->format('Y');
			$addarray['month']    = $row->published_at->format('M');
		}
		$addarray['note']         = $row->note;
		$addarray['number']       = $row->number;
		$addarray['organization'] = $row->organization;
		$addarray['pages']        = $row->pages;
		$addarray['publisher']    = $row->publisher;
		$addarray['series']       = $row->series;
		$addarray['school']       = $row->school;
		$addarray['url']          = $row->url;
		$addarray['volume']       = $row->volume;
		if ($row->journal)
		{
			$addarray['issn']     = $row->isbn;
		}
		else
		{
			$addarray['isbn']     = $row->isbn;
		}
		$addarray['doi']          = $row->doi;

		/*$addarray['language']         = $row->language;
		$addarray['accession_number'] = $row->accession_number;
		$addarray['short_title']      = html_entity_decode($row->short_title);
		$addarray['author_address']   = $row->author_address;
		$addarray['keywords']         = str_replace("\r\n", ', ', $row->keywords);
		$addarray['abstract']         = $row->abstract;
		$addarray['call_number']      = $row->call_number;
		$addarray['label']            = $row->label;
		$addarray['research_notes']   = $row->research_notes;*/

		$bibtex = new Parser();
		$bibtex->addEntry($addarray);

		return $bibtex->bibTex();
	}
}
