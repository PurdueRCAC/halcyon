<?php
namespace App\Modules\Publications\Helpers;

use App\Modules\Publications\Helpers\Downloadable;
use App\Modules\Publications\Models\Publication;
use Exception;

/**
 * Citations class for downloading a citation of a specific file type
 */
class Download
{
	/**
	 * Download formt
	 *
	 * @var string
	 */
	protected $_format = 'Bibtex';

	/**
	 * Citation object
	 *
	 * @var Publication
	 */
	protected $_reference = null;

	/**
	 * List of formatters
	 *
	 * @var array<string,Downloadable>
	 */
	protected $_formatters = array();

	/**
	 * Mime type
	 *
	 * @var string
	 */
	protected $_mime = '';

	/**
	 * File extension
	 *
	 * @var  string
	 */
	protected $_extension = '';

	/**
	 * Constructor
	 *
	 * @param   Publication  $reference
	 * @param   string  $format
	 * @return  void
	 */
	public function __construct(Publication $reference=null, $format='Bibtex')
	{
		$this->setFormat($format);
		$this->setReference($reference);
	}

	/**
	 * Set the format
	 *
	 * @param   string  $format
	 * @return  self
	 */
	public function setFormat($format): self
	{
		$this->_format = ucfirst(trim(strtolower($format)));

		return $this;
	}

	/**
	 * Get the format
	 *
	 * @return  string
	 */
	public function getFormat(): string
	{
		return $this->_format;
	}

	/**
	 * Set the reference
	 *
	 * @param   Publication  $reference
	 * @return  self
	 */
	public function setReference(Publication $reference): self
	{
		$this->_reference = $reference;

		return $this;
	}

	/**
	 * Get the reference
	 *
	 * @return  Publication
	 */
	public function getReference(): Publication
	{
		return $this->_reference;
	}

	/**
	 * Set the mime type
	 *
	 * @param   string  $mime
	 * @return  self
	 */
	public function setMimeType($mime): self
	{
		$this->_mime = $mime;

		return $this;
	}

	/**
	 * Get the mime type
	 *
	 * @return  string
	 */
	public function getMimeType(): string
	{
		return $this->_mime;
	}

	/**
	 * Set the extension
	 *
	 * @param   string  $extension
	 * @return  self
	 */
	public function setExtension($extension): self
	{
		$this->_extension = $extension;

		return $this;
	}

	/**
	 * Get the extension
	 *
	 * @return  string
	 */
	public function getExtension(): string
	{
		return $this->_extension;
	}

	/**
	 * Set formatter for specified format
	 *
	 * @param   Downloadable  $formatter
	 * @param   string  $format
	 * @return  self
	 */
	public function setFormatter(Downloadable $formatter, $format=''): self
	{
		$format = ($format) ? $format : $this->_format;

		$this->_formatters[$format] = $formatter;

		return $this;
	}

	/**
	 * Get the formatter object, if set
	 *
	 * @param   string  $format  Format to get
	 * @return  Downloadable|null
	 */
	public function getFormatter($format='')
	{
		$format = ($format) ? $format : $this->_format;

		return (isset($this->_formatters[$format])) ? $this->_formatters[$format] : null;
	}

	/**
	 * Format a record
	 *
	 * @param   Publication|null  $reference  Record to format
	 * @return  string
	 * @throws  Exception
	 */
	public function formatReference($reference=null): string
	{
		if (!$reference)
		{
			$reference = $this->getReference();
		}

		if (!$reference || (!is_array($reference) && !is_object($reference)))
		{
			return '';
		}

		$format = $this->getFormat();

		$formatter = $this->getFormatter($format);

		if (!$formatter || !is_object($formatter))
		{
			$cls = __NAMESPACE__ . '\\Downloadable\\' . $format;

			if (!class_exists($cls))
			{
				throw new Exception(trans('Download format unavailable.'), 500);
			}

			$formatter = new $cls();

			if (!$formatter instanceof Downloadable)
			{
				throw new Exception(trans('Invalid download formatter specified.'), 500);
			}

			$this->setFormatter($formatter, $format);
		}

		$this->setExtension($formatter->extension());
		$this->setMimeType($formatter->mimetype());

		return $formatter->format($reference);
	}
}
