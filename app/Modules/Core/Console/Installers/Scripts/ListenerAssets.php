<?php

namespace App\Modules\Core\Console\Installers\Scripts;

use Illuminate\Console\Command;
use App\Modules\Core\Console\Installers\SetupScript;
use App\Modules\Core\Console\Installers\Traits\BlockMessage;

class ListenerAssets implements SetupScript
{
    use BlockMessage;

    /**
     * Fire the install script
     * @param  Command $command
     * @return mixed
     */
    public function fire(Command $command)
    {
        if ($command->option('verbose'))
        {
            $command->blockMessage('Listeners', 'Publishing listener assets ...', 'comment');
        }

        if ($command->option('verbose'))
        {
            $command->call('listener:publish');
            return;
        }

        $command->callSilent('listener:publish');
    }
}
