<?php
namespace App\Halcyon\Http\Concerns;

trait SendsAlerts
{
    /**
     * Send a success message
     */
    protected function success(string $msg, array $parameters = []): void
    {
        $this->sendAlert('success', $msg, $parameters);
    }

    /**
     * Send an error message
     */
    protected function error(string $msg, array $parameters = []): void
    {
        $this->sendAlert('error', $msg, $parameters);
    }

    /**
     * Send a message
     */
    private function sendAlert(string $type, string $msg, array $parameters = []): void
    {
        session()->flash($type, trans($msg, $parameters));
    }
}
