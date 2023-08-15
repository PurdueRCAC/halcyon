<?php

namespace App\Modules\Core\Console\Installers\Scripts;

use Illuminate\Console\Command;
use App\Modules\Core\Console\Installers\SetupScript;
use App\Modules\Core\Console\Installers\Traits\BlockMessage;

class ThemeAssets implements SetupScript
{
    use BlockMessage;

    /**
     * Fire the install script
     * @param  Command $command
     * @return void
     */
    public function fire(Command $command)
    {
        if ($command->option('verbose'))
        {
            $command->blockMessage('Themes', 'Publishing theme assets ...', 'comment');
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
