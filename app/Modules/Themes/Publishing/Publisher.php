<?php

namespace App\Modules\Themes\Publishing;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use App\Modules\Themes\Contracts\PublisherInterface;
//use App\Modules\Themes\Contracts\RepositoryInterface;
use App\Modules\Themes\Entities\Theme;

abstract class Publisher implements PublisherInterface
{
	/**
	 * The name of theme used.
	 *
	 * @var string
	 */
	protected $theme;

	/**
	 * The modules repository instance.
	 * @var RepositoryInterface
	 */
	protected $repository;

	/**
	 * The laravel console instance.
	 *
	 * @var Command
	 */
	protected $console;

	/**
	 * The success message will displayed at console.
	 *
	 * @var string
	 */
	protected $success;

	/**
	 * The error message will displayed at console.
	 *
	 * @var string
	 */
	protected $error = '';

	/**
	 * Determine whether the result message will shown in the console.
	 *
	 * @var bool
	 */
	protected $showMessage = true;

	/**
	 * The constructor.
	 *
	 * @param Theme $theme
	 */
	public function __construct(Theme $theme)
	{
		$this->theme = $theme;
	}

	/**
	 * Show the result message.
	 *
	 * @return self
	 */
	public function showMessage(): self
	{
		$this->showMessage = true;

		return $this;
	}

	/**
	 * Hide the result message.
	 *
	 * @return self
	 */
	public function hideMessage(): self
	{
		$this->showMessage = false;

		return $this;
	}

	/**
	 * Get theme instance.
	 *
	 * @return Theme
	 */
	public function getTheme(): self
	{
		return $this->theme;
	}

	/**
	 * Set modules repository instance.
	 * @param RepositoryInterface $repository
	 * @return $this
	 */
	public function setRepository(RepositoryInterface $repository): self
	{
		$this->repository = $repository;

		return $this;
	}

	/**
	 * Get modules repository instance.
	 *
	 * @return RepositoryInterface
	 */
	public function getRepository(): RepositoryInterface
	{
		return $this->repository;
	}

	/**
	 * Set console instance.
	 *
	 * @param Command $console
	 * @return $this
	 */
	public function setConsole(Command $console): self
	{
		$this->console = $console;

		return $this;
	}

	/**
	 * Get console instance.
	 *
	 * @return Command
	 */
	public function getConsole(): Command
	{
		return $this->console;
	}

	/**
	 * Get laravel filesystem instance.
	 *
	 * @return Filesystem
	 */
	public function getFilesystem(): Filesystem
	{
		return $this->repository->getFiles();
	}

	/**
	 * Get destination path.
	 *
	 * @return string
	 */
	abstract public function getDestinationPath(): string;

	/**
	 * Get source path.
	 *
	 * @return string
	 */
	abstract public function getSourcePath(): string;

	/**
	 * Publish something.
	 *
	 * @throws \RuntimeException
	 */
	public function publish(): void
	{
		if (!$this->console instanceof Command)
		{
			$message = "The 'console' property must instance of \\Illuminate\\Console\\Command.";

			throw new \RuntimeException($message);
		}

		if (!$this->getFilesystem()->isDirectory($sourcePath = $this->getSourcePath()))
		{
			return;
		}

		if (!$this->getFilesystem()->isDirectory($destinationPath = $this->getDestinationPath()))
		{
			$this->getFilesystem()->makeDirectory($destinationPath, 0775, true);
		}

		if ($this->getFilesystem()->copyDirectory($sourcePath, $destinationPath))
		{
			if ($this->showMessage === true)
			{
				$this->console->line("<info>Published</info>: {$this->theme->getStudlyName()}");
			}
		}
		else
		{
			$this->console->error($this->error);
		}
	}
}
