<?php
namespace App\Modules\Publications\Helpers;

use App\Modules\Publications\Models\Publication;

/**
 * Abstract class for citations download
 */
abstract class Downloadable
{
	/**
	 * Mime type
	 *
	 * @var string
	 */
	protected $mimetype = 'application/text';

	/**
	 * File extension
	 *
	 * @var string
	 */
	protected $extension = 'txt';

	/**
	 * Get the mime type
	 *
	 * @return string
	 */
	public function mimetype(): string
	{
		return $this->mimetype;
	}

	/**
	 * Get the file extension
	 *
	 * @return string
	 */
	public function extension(): string
	{
		return $this->extension;
	}

	/**
	 * Format the file
	 *
	 * @param  Publication $row
	 * @return string
	 */
	public function format(Publication $row): string
	{
		return $row->toString();
	}
}
