<?php

namespace Modules\Core\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class PublishModuleAssetsCommand extends Command
{
    /**
     * @var string
     */
    protected $name = 'asgard:publish:module';

    /**
     * @var string
     */
    protected $description = 'Publish module assets';

    /**
     * @return void
     */
    public function handle()
    {
        $this->call('module:publish', ['module' => $this->argument('module')]);
    }

    /**
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['module', InputArgument::REQUIRED, 'The module name'],
        ];
    }
}
