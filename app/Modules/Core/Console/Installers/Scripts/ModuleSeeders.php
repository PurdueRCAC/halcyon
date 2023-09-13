<?php

namespace App\Modules\Core\Console\Installers\Scripts;

use Illuminate\Console\Command;
use App\Modules\Core\Console\Installers\SetupScript;

class ModuleSeeders implements SetupScript
{
    /**
     * @var array<int,string>
     */
    protected $modules = [
        'Core',
        'Listeners',
        'Mailer',
        'Media',
        'Menus',
        'Pages',
        'Themes',
        'Users',
        'Widgets',
    ];

    /**
     * Fire the install script
     * @param  Command $command
     * @return void
     */
    public function fire(Command $command)
    {
        if ($command->option('verbose'))
        {
            $command->line('Running the module seeds ...');
        }

        foreach ($this->modules as $module)
        {
            if ($command->option('verbose'))
            {
                $command->call('module:seed', ['module' => $module]);
                continue;
            }
            $command->callSilent('module:seed', ['module' => $module]);
        }
    }
}
