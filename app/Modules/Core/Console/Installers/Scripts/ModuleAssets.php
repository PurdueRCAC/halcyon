<?php

namespace App\Modules\Core\Console\Installers\Scripts;

use Illuminate\Console\Command;
use App\Modules\Core\Console\Installers\SetupScript;

class ModuleAssets implements SetupScript
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
            $command->blockMessage('Module assets', 'Publishing module assets ...', 'comment');
        }

        /*foreach ($this->modules as $module)
        {
            if ($command->option('verbose'))
            {
                $command->call('module:publish', ['module' => $module]);
                continue;
            }
            $command->callSilent('module:publish', ['module' => $module]);
        }*/

        if ($command->option('verbose'))
        {
            $command->call('module:publish');
            return;
        }

        $command->callSilent('module:publish');
    }
}
