<?php

namespace App\Modules\Core\Console\Installers\Scripts;

use Illuminate\Console\Command;
use App\Modules\Core\Console\Installers\SetupScript;

class ListenerAssets implements SetupScript
{
    /**
     * Fire the install script
     * @param  Command $command
     * @return mixed
     */
    public function fire(Command $command)
    {
        if ($command->option('verbose'))
        {
            $command->line('Publishing listener assets ...');
        }

        if ($command->option('verbose'))
        {
            $command->call('listener:publish');
            return;
        }

        $command->callSilent('listener:publish');
    }
}
