<?php

namespace App\Modules\Listeners\Console;

use Illuminate\Console\Command;
use App\Modules\Listeners\Process\Installer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class InstallCommand extends Command
{
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'listener:install';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Install the specified listener by given package name (vendor/name)';

	/**
	 * Create a new command instance.
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 */
	public function handle()
	{
		if (is_null($this->argument('name')))
		{
			$this->installFromFile();

			return;
		}

		$this->install(
			$this->argument('name'),
			$this->argument('version'),
			$this->option('type'),
			$this->option('tree')
		);
	}

	/**
	 * Install modules from modules.json file.
	 */
	protected function installFromFile()
	{
		if (!file_exists($path = base_path('listeners.json')))
		{
			$dirs = $this->laravel['files']->directories($this->laravel['themes']->getPath());

			foreach ($dirs as $dir)
			{
				if ($found = $this->laravel['listener']->find(basename($dir)))
				{
					continue;
				}

				$this->laravel['listener']->registerListener(new Listener(basename($dir), $dir));
			}
			//$this->error("File 'themes.json' does not exist in your project root.");

			return;
		}

		$themes = Json::make($path);

		$dependencies = $themes->get('require', []);

		foreach ($dependencies as $theme)
		{
			$theme = collect($theme);

			$this->install(
				$theme->get('name'),
				$theme->get('version'),
				$theme->get('type')
			);
		}
	}

	/**
	 * Install the specified module.
	 *
	 * @param string $name
	 * @param string $version
	 * @param string $type
	 * @param bool   $tree
	 */
	protected function install($name, $version = 'dev-master', $type = 'composer', $tree = false)
	{
		$installer = new Installer(
			$name,
			$version,
			$type ?: $this->option('type'),
			$tree ?: $this->option('tree')
		);

		$installer->setRepository($this->laravel['themes']);

		$installer->setConsole($this);

		if ($timeout = $this->option('timeout'))
		{
			$installer->setTimeout($timeout);
		}

		if ($path = $this->option('path'))
		{
			$installer->setPath($path);
		}

		$installer->run();

		if (!$this->option('no-update'))
		{
			$this->call('theme:update', [
				'theme' => $installer->getThemeName(),
			]);
		}
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['name', InputArgument::OPTIONAL, 'The name of the listener to be installed.'],
			['version', InputArgument::OPTIONAL, 'The version of the listener to be installed.'],
		];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			['timeout', null, InputOption::VALUE_OPTIONAL, 'The process timeout.', null],
			['path', '/app/Listeners', InputOption::VALUE_OPTIONAL, 'The installation path.', null],
			['type', null, InputOption::VALUE_OPTIONAL, 'The type of installation.', null],
			['tree', null, InputOption::VALUE_NONE, 'Install the theme as a git subtree', null],
			['no-update', null, InputOption::VALUE_NONE, 'Disables the automatic update of the dependencies.', null],
		];
	}
}
