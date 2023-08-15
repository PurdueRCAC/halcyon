<?php

namespace App\Modules\Core\Console\Installers\Traits;

trait SectionMessage
{
    /**
     * Output a section message
     *
     * @param string $title
     * @param string $message
     * @return void
     */
    public function sectionMessage($title, $message)
    {
        $formatter = $this->getHelperSet()->get('formatter');
        $formattedLine = $formatter->formatSection(
            $title,
            $message
        );
        $this->line($formattedLine);
    }
}
