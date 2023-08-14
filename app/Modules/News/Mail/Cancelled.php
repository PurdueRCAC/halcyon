<?php

namespace App\Modules\News\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Modules\News\Models\Article;
use App\Modules\News\Models\Association;

class Cancelled extends Mailable
{
	use Queueable, SerializesModels;

	/**
	 * The article
	 *
	 * @var Article
	 */
	protected $article;

	/**
	 * User association
	 *
	 * @var Association
	 */
	protected $association;

	/**
	 * Message headers
	 *
	 * @var Headers
	 */
	protected $headers;

	/**
	 * Create a new message instance.
	 *
	 * @param  Association $association
	 * @return void
	 */
	public function __construct(Association $association)
	{
		$this->article = $association->article;
		$this->association = $association;
	}

	/**
	 * Get the message headers.
	 *
	 * @return Headers
	 */
	public function headers(): Headers
	{
		if (!($this->headers instanceof Headers))
		{
			$this->headers = new Headers(
				messageId: null,
				references: [],
				text: [
					'X-Target-Object' => $this->association->id,
					'X-Object' => $this->article->id,
				],
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
		return new Envelope(
			tags: ['news', 'news-reservation-canceled'],
			metadata: [
				'news_id' => $this->article->id,
			],
		);
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('news::mail.cancelled')
					->subject(trans('news::news.cancelled event registration'))
					->with([
						'article' => $this->article,
						'association' => $this->association,
					]);
	}
}
