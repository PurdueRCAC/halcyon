<?php

namespace App\Modules\Core\Services;

use Symfony\Component\Process\Process;

class Composer extends \Illuminate\Support\Composer
{
    /**
     * @var \Closure
     */
    private $output;

    /**
     * Enable real time output of all commands.
     *
     * @param object $command
     * @return void
     */
    public function enableOutput($command)
    {
        $this->output = function ($type, $buffer) use ($command)
        {
            if (Process::ERR === $type)
            {
                $command->info(trim('[ERR] > ' . $buffer));
            }
            else
            {
                $command->info(trim('> ' . $buffer));
            }
        };
    }

    /**
     * Disable real time output of all commands.
     *
     * @return void
     */
    public function disableOutput()
    {
        $this->output = null;
    }

    /**
     * Update all composer packages.
     *
     * @param  string $package
     * @return void
     */
    public function update($package = null)
    {
        $package = '"' . $package . '"';

        $process = $this->getProcess();
        $process->setCommandLine(trim($this->findComposer() . ' update ' . $package));
        $process->run($this->output);
    }

    /**
     * Require a new composer package.
     *
     * @param  string $package
     * @return void
     */
    public function install($package)
    {
        $package = '"' . $package . '"';

        $process = $this->getProcess();
        $process->setCommandLine(trim($this->findComposer() . ' require ' . $package));
        $process->run($this->output);
    }

    /**
     * @return void
     */
    public function dumpAutoload()
    {
        $process = $this->getProcess();
        $process->setCommandLine(trim($this->findComposer() . ' dump-autoload -o'));
        $process->run($this->output);
    }

    /**
     * @param string $package
     * @return void
     */
    public function remove($package)
    {
        $package = '"' . $package . '"';

        $process = $this->getProcess();
        $process->setCommandLine(trim($this->findComposer() . ' remove ' . $package));
        $process->run($this->output);
    }
}
