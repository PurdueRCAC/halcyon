<?php

namespace App\Modules\Core\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use App\Modules\Core\Console\Installers\Installer;
use App\Modules\Core\Console\Installers\Traits\BlockMessage;
use App\Modules\Core\Console\Installers\Traits\SectionMessage;

class InstallCommand extends Command
{
	use BlockMessage, SectionMessage;

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'halcyon:install';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Install Halcyon';

	/**
	 * @var Installer
	 */
	private $installer;

	/**
	 * Create a new command instance.
	 *
	 * @param Installer $installer
	 * @internal param Filesystem $finder
	 * @internal param Application $app
	 * @internal param Composer $composer
	 */
	public function __construct(Installer $installer)
	{
		parent::__construct();

		$this->getLaravel()['env'] = 'local';

		$this->installer = $installer;
	}

	/**
	 * Execute the actions
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$this->blockMessage('Welcome!', 'Starting the installation process...', 'comment');

		$success = $this->installer->stack([
			\App\Modules\Core\Console\Installers\Scripts\ProtectInstaller::class,
			\App\Modules\Core\Console\Installers\Scripts\CreateEnvFile::class,
			\App\Modules\Core\Console\Installers\Scripts\ConfigureDatabase::class,
			\App\Modules\Core\Console\Installers\Scripts\ConfigureAppUrl::class,
			\App\Modules\Core\Console\Installers\Scripts\SetAppKey::class,
			\App\Modules\Core\Console\Installers\Scripts\ConfigureUserProvider::class,
			\App\Modules\Core\Console\Installers\Scripts\ModuleMigrator::class,
			\App\Modules\Core\Console\Installers\Scripts\ModuleSeeders::class,
			\App\Modules\Core\Console\Installers\Scripts\ModuleAssets::class,
			\App\Modules\Core\Console\Installers\Scripts\ThemeAssets::class,
			\App\Modules\Core\Console\Installers\Scripts\UnignoreComposerLock::class,
			\App\Modules\Core\Console\Installers\Scripts\UnignorePackageLock::class,
			\App\Modules\Core\Console\Installers\Scripts\SetInstalledFlag::class,
		])->install($this);

		if ($success)
		{
			$this->info('Platform ready! You can now login with your username and password at /admin');
		}
	}

	protected function getOptions()
	{
		return [
			['force', 'f', InputOption::VALUE_NONE, 'Force the installation, even if already installed'],
		];
	}
}
