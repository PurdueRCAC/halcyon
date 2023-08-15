<?php

namespace App\Modules\Core\Console\Installers\Scripts;

use Illuminate\Console\Command;
use App\Modules\Core\Console\Installers\SetupScript;
use App\Modules\Core\Console\Installers\Traits\BlockMessage;

class ModuleSeeders implements SetupScript
{
    use BlockMessage;

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
        if ($command->option('verbose')) {
            $command->blockMessage('Seeds', 'Running the module seeds ...', 'comment');
        }

        foreach ($this->modules as $module) {
            if ($command->option('verbose')) {
                $command->call('module:seed', ['module' => $module]);
                continue;
            }
            $command->callSilent('module:seed', ['module' => $module]);
        }
    }
}
