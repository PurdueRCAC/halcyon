<?php

namespace App\Modules\Core\Console\Installers\Scripts;

use Illuminate\Console\Command;
use App\Modules\Core\Console\Installers\SetupScript;

class ModuleMigrator implements SetupScript
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
            $command->blockMessage('Migrations', 'Starting the module migrations ...', 'comment');
        }

        foreach ($this->modules as $module)
        {
            if ($command->option('verbose'))
            {
                $command->call('module:migrate', ['module' => $module]);
                continue;
            }
            $command->callSilent('module:migrate', ['module' => $module]);
        }
    }
}
