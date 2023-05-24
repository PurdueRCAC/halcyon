<?php

namespace App\Modules\Core\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use App\Modules\Core\Models\Extension;

class DeleteModuleCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'delete:module';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a module and optionally its migrations';

    /**
     * @var Filesystem
     */
    private $finder;

    /**
     * @param Filesystem $finder
     * @return void
     */
    public function __construct(Filesystem $finder)
    {
        parent::__construct();

        $this->finder = $finder;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $module = $this->argument('module');

        $extra = '';
        if ($this->option('migrations') === true)
        {
            $extra = ' and reset its tables';
        }
        if ($this->confirm("Are you sure you wish to delete the [$module] module{$extra}?") === false)
        {
            $this->info('Nothing was deleted');

            return;
        }

        $modulePath = config('modules.paths.modules') . '/' . $module;

        if ($this->finder->exists($modulePath) === false)
        {
            $this->error('This module does not exist');

            return;
        }

        if ($this->isCore($module) === true)
        {
            $this->error('You cannot remove a core module.');

            return;
        }

        if ($this->option('migrations') === true)
        {
            $this->call('module:migrate-reset', ['module' => $module]);
        }

        $this->removePermissionsFor($module);

        $this->finder->deleteDirectory($modulePath);
        $this->info('Module successfully deleted');
    }

    /**
     * @param string $module
     * @return void
     */
    private function removePermissionsFor($module)
    {
        (new PermissionsRemover($module))->removeAll();

        $this->info("All permissions for [$module] have been removed");
    }

    /**
     * @param string $module
     * @return bool
     */
    private function isCore($module): bool
    {
        $mod = Extension::query()
            ->where('type', '=', 'module')
            ->where('element', '=', $module)
            ->first();

        return $mod->protected ? true : false;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['module', InputArgument::REQUIRED, 'The module name'],
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
            ['migrations', 'm', InputOption::VALUE_NONE, 'Reset the module migrations', null],
        ];
    }
}
