<?php

namespace Modules\Core\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class PublishThemeAssetsCommand extends Command
{
    /**
     * @var string
     */
    protected $name = 'asgard:publish:theme';

    /**
     * @var string
     */
    protected $description = 'Publish theme assets';

    /**
     * @return void
     */
    public function handle()
    {
        $theme = $this->argument('theme', null);

        if (!empty($theme))
        {
            $this->call('stylist:publish', ['theme' => $this->argument('theme')]);
        }
        else
        {
            $this->call('stylist:publish');
        }
    }

    /**
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['theme', InputArgument::OPTIONAL, 'Name of the theme you wish to publish'],
        ];
    }
}
