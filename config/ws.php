<?php

return [

    /*
    |--------------------------------------------------------------------------
    | User ID
    |--------------------------------------------------------------------------
    |
    | ID of user account to use.
    |
    */

    'user_id' => 61292,

    /*
    |--------------------------------------------------------------------------
    | Whitelisted IPs
    |--------------------------------------------------------------------------
    |
    | Allowed IPs
    |
    */

    'whitelist' => [
        '::1', // localhost
        '127.0.0.1', // localhost
        '128.211.157.46',  // web.rcac.purdue.edu

        // Services, misc.
        '128.211.157.100', // bastion.rcac.purdue.edu
        '128.211.157.26',  // ondemand.rcac
        '128.211.157.21',  // newxenon.rcac.purdue.edu
        '172.18.64.196',   // centralservices.rcac.purdue.edu
        '128.211.157.132', // dpecher.rcac.purdue.edu
        '128.210.189.82',  // duvel.rcac.purdue.edu
        '128.211.157.49',  // client8.rcac.purdue.edu
        '172.18.64.46',    // warden.rcac.purdue.edu
        '172.18.64.47',    // warden-new.rcac.purdue.edu
        '172.18.64.52',    // warden-test.rcac.purdue.edu
        '172.18.64.71',    // warden2.rcac.purdue.edu
        '128.211.149.178',
        '128.210.189.73',  // odin.rcac.purdue.edu
        '172.31.96.30',    // tools.itap.purdue.edu
        '172.18.64.182',   // storagetools.rcac.purdue.edu

        // Anvil
        '128.211.157.39',  // amie.anvil.rcac.purdue.edu
        '172.18.95.224',   // adm.anvil.rcac.purdue.edu

        // Bell
        '128.211.133.54',  // gateway.bell
        '172.18.15.11',    // bell-adm.rcac.purdue.edu

        // Brown
        '128.211.148.144', // ondemand.brown
        '128.211.149.106', // brown-adm.rcac.purdue.edu
        '128.211.149.117', // warden.brown.rcac.purdue.edu
        '128.211.149.118', // gateway.brown

        // Gilbreth
        '128.211.133.11',  // gilbreth-adm.rcac.purdue.edu
        '128.211.133.19',  // gateway.gilbreth
        '128.211.133.20',  // warden.gilbreth.rcac.purdue.edu

        // Halstead
        '128.211.148.10',  // halstead-adm.rcac.purdue.edu
        '128.211.148.29',  // warden.halstead.rcac.purdue.edu
        '128.211.148.30',  // gateway.halstead
        '128.211.157.252', // workshop.halstead.rcac.purdue.edu

        // Hammer
        '128.211.158.41',  // hammer-adm.rcac.purdue.edu

        // Mack
        '172.18.8.27',     // mack-adm.rcac.purdue.edu
        '172.18.8.33',     // gateway.mack
        '172.18.66.17',    // ondemand.mack

        // Rice
        '128.211.148.151', // warden.rice.rcac.purdue.edu
        '128.211.148.152', // gateway.rice

        // Scholar
        '128.211.149.166', // scholar-adm.rcac.purdue.edu
        '128.211.149.179', // gateway.scholar
        '128.211.149.180', // scholar-zfs.rcac.purdue.edu
        '128.211.157.253', // gateway.scholar (temporarily housed on kvm-03)

        // Snyder
        '128.211.148.212', // warden.snyder.rcac.purdue.edu
        '128.211.148.213', // gateway.snyder
    ],

    /*
    |--------------------------------------------------------------------------
    | Whitelisted IP ranges
    |--------------------------------------------------------------------------
    |
    | A list of allowed ranges [lower, higher]
    |
    */

    'ranges' => [
        //['127.0.0.0', '127.0.0.100'],
    ]
];
