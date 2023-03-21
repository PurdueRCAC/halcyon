<?php

namespace App\Modules\Queues\Mail\Traits;

use Illuminate\Mail\Mailables\Headers;
use Illuminate\Mail\Mailables\Envelope;

trait HeadersAndTags
{
	/**
	 * The order instance.
	 *
	 * @var Headers
	 */
	protected $headers;

	/**
	 * List of headers to apply
	 *
	 * @var array<string,string>
	 */
	protected $mailHeaders = array();

	/**
	 * List of tags
	 *
	 * @var array<int,string>
	 */
	protected $mailTags = array('queues');

	/**
	 * List of metadata
	 *
	 * @var array<int,string>
	 */
	protected $mailMetadata = array();

	/**
	 * Get the message headers.
	 *
	 * @return Headers
	 */
	public function headers(): Headers
	{
		if (!$this->headers)
		{
			if (isset($this->user) && !isset($this->headers['X-Target-User']))
			{
				$this->mailHeaders['X-Target-User'] = $this->user->id;
			}

			$this->headers = new Headers(
				messageId: null,
				references: [],
				text: $this->mailHeaders,
			);
		}
		return $this->headers;
	}

	/**
	 * Get the message envelope.
	 *
	 * @return Envelope
	 */
	public function envelope(): Envelope
	{
		if (isset($this->user) && !isset($this->mailMetadata['user_id']))
		{
			$this->mailMetadata['user_id'] = $this->user->id;
		}

		return new Envelope(
			tags: $this->mailTags,
			metadata: $this->mailMetadata,
		);
	}
}
