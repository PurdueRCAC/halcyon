<?php

namespace App\Modules\Core\Console\Installers\Scripts;

use Illuminate\Console\Command;
use App\Modules\Core\Console\Installers\SetupScript;
use App\Modules\Core\Console\Installers\Writers\EnvFileWriter;

class CreateEnvFile implements SetupScript
{
    /**
     * @var EnvFileWriter
     */
    protected $env;

    /**
     * @param EnvFileWriter $env
     */
    public function __construct(EnvFileWriter $env)
    {
        $this->env = $env;
    }

    /**
     * @var Command
     */
    protected $command;

    /**
     * Fire the install script
     * @param  Command $command
     * @return mixed
     */
    public function fire(Command $command)
    {
        $this->command = $command;

        $this->env->create();

        $command->info('Successfully created .env file');
    }
}
