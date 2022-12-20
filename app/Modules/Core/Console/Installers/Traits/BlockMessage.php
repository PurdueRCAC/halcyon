<?php

namespace Modules\Core\Console\Installers\Traits;

trait BlockMessage
{
    /**
     * Set a block message
     *
     * @param string $title
     * @param string $message
     * @param string $style
     * @return void
     */
    public function blockMessage($title, $message, $style = 'info')
    {
        $formatter = $this->getHelperSet()->get('formatter');
        $errorMessages = [$title, $message];
        $formattedBlock = $formatter->formatBlock($errorMessages, $style, true);
        $this->line($formattedBlock);
    }
}
