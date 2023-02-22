<?php

namespace App\Modules\Media\Events;

use Illuminate\Http\Request;

class FileUpdate
{
	/**
	 * @var string
	 */
	private $disk;

	/**
	 * @var string
	 */
	private $path;

	/**
	 * @var \Illuminate\Http\UploadedFile
	 */
	private $file;

	/**
	 * FileUpdate constructor.
	 *
	 * @param Request $request
	 */
	public function __construct(Request $request)
	{
		$this->disk = (string)$request->input('disk', 'public');
		$this->path = (string)$request->input('path');
		$this->file = $request->file('file');
	}

	/**
	 * @return string
	 */
	public function disk(): string
	{
		return $this->disk;
	}

	/**
	 * @return string
	 */
	public function path(): string
	{
		if ($this->path)
		{
			return $this->path . '/' . $this->file->getClientOriginalName();
		}

		return $this->file->getClientOriginalName();
	}
}
