<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | The base URL to the Rancher service's API. All calls are made relative to
    | this.
    |
    */
    'url' => env('RANCHER_URL', 'https://geddes.rcac.purdue.edu/v3/'),

    /*
    |--------------------------------------------------------------------------
    | Credentials
    |--------------------------------------------------------------------------
    */
    'access_key' => env('RANCHER_ACCESS_KEY'),
    'secret_key' => env('RANCHER_SECRET_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Resource Quotas
    |--------------------------------------------------------------------------
    |
    | When you create a resource quota, you are configuring the pool of
    | resources available to the project. You can set the following resource
    | limits for the following resource types.
    |
    | Ref: https://rancher.com/docs/rancher/v2.5/en/project-admin/resource-quotas/quota-type-reference/
    |
    */
    'quotas' => [
        // The maximum amount of CPU (in millicores) allocated to the project/namespace.
        'cpu_limit_project' => 0, // Total cores purchased by PI
        'cpu_limit_namespace' => 0.25, // of Project Limit

        // The maximum amount of memory (in bytes) allocated to the project/namespace.
        'memory_limit_project' => 0, // Total memory purchased by PI
        'memory_limit_namespace' => 0.25, // of Project Limit

        // The minimum amount of storage (in gigabytes) guaranteed to the project/namespace.
        'storage_reservation' => '100 Gb', // Amount of TB Purchased by PI

        // The maximum number of pods that can exist in the project/namespace in a non-terminal
        // state (i.e., pods with a state of `.status.phase in (Failed, Succeeded)` equal to true).
        'pod_limit_project' => 200,
        'pod_limit_namespace' => 50,

        // When setting resource quotas, if you set anything related to CPU or Memory (i.e. limits
        // or reservations) on a project / namespace, all containers will require a respective CPU
        // or Memory field set during creation.
        'container_limit_cpu' => '100 mCPU',
        'container_limit_memory' => '128 MiB',
    ],
];
