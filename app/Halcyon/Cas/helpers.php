<?php

if (!function_exists('cas'))
{
    /**
     * Initiate CAS hook.
     *
     * @return App\Halcyon\Cas\CasManager
     */
    function cas()
    {
        return app('cas');
    }
}
