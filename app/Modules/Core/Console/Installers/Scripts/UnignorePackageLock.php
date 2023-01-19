<?php

namespace App\Modules\Core\Console\Installers\Scripts;

use Illuminate\Console\Command;
use App\Modules\Core\Console\Installers\SetupScript;

class UnignorePackageLock implements SetupScript
{
    const PACKAGE_LOCK = 'package-lock.json';

    /**
     * Fire the install script
     *
     * @param  Command $command
     * @return void
     */
    public function fire(Command $command)
    {
        $gitignorePath = base_path('.gitignore');

        if (!$this->gitignoreContainsPackageLock($gitignorePath))
        {
            return;
        }

        $removePackageLock = $command->confirm('Do you want to remove package-lock.json from .gitignore ?', true);

        if ($removePackageLock)
        {
            $out = $this->getGitignoreLinesButPackageLock($gitignorePath);
            $this->writeNewGitignore($gitignorePath, $out);
        }
    }

    /**
     * @param string $gitignorePath
     * @return bool
     */
    private function gitignoreContainsPackageLock($gitignorePath)
    {
        return file_exists($gitignorePath) && strpos(file_get_contents($gitignorePath), self::PACKAGE_LOCK) !== false;
    }

    /**
     * @param string $gitignorePath
     * @return array<int,string>
     */
    private function getGitignoreLinesButPackageLock($gitignorePath)
    {
        $data = file($gitignorePath);
        $out = [];
        foreach ($data as $line)
        {
            if (trim($line) !== self::PACKAGE_LOCK)
            {
                $out[] = $line;
            }
        }

        return $out;
    }

    /**
     * @param string $gitignorePath
     * @param array<int,string> $out
     */
    private function writeNewGitignore($gitignorePath, $out)
    {
        $fp = fopen($gitignorePath, 'wb+');
        flock($fp, LOCK_EX);
        foreach ($out as $line)
        {
            fwrite($fp, $line);
        }
        flock($fp, LOCK_UN);
        fclose($fp);
    }
}
