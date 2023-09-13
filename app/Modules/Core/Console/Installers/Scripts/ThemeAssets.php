<?php

namespace App\Modules\Core\Console\Installers\Scripts;

use Illuminate\Console\Command;
use App\Modules\Core\Console\Installers\SetupScript;

class ThemeAssets implements SetupScript
{
    /**
     * Fire the install script
     * @param  Command $command
     * @return void
     */
    public function fire(Command $command)
    {
        if ($command->option('verbose'))
        {
            $command->line('Publishing theme assets ...');
        }

        if ($command->option('verbose'))
        {
            //$command->call('stylist:publish');
            $command->call('theme:publish');
            return;
        }

        //$command->callSilent('stylist:publish');
        $command->callSilent('theme:publish');
    }
}
