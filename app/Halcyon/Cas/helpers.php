<?php

if (!function_exists('cas'))
{
    /**
     * Initiate CAS hook.
     *
     * @return \Subfission\Cas\CasManager
     */
    function cas()
    {
        return app('cas');
    }
}
