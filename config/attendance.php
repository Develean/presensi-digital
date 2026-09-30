<?php

return [
    /*
    |--------------------------------------------------------------------------
    | School Wi-Fi & Network Restriction Settings
    |--------------------------------------------------------------------------
    |
    | When enabled, self-attendance can only be submitted from the specified
    | school IP addresses or subnets.
    |
    */
    'enforce_wifi_restriction' => env('ENFORCE_WIFI_RESTRICTION', false),

    /*
    | Subnets / IPs allowed for self-attendance.
    | Supports CIDR notation (e.g., 192.168.1.0/24) and single IPs.
    | Default includes local development loops and standard LAN subnets.
    */
    'school_wifi_subnets' => array_filter(array_map('trim', explode(',', env('SCHOOL_WIFI_SUBNETS', '127.0.0.1,::1,192.168.0.0/16,10.0.0.0/8,172.16.0.0/12')))),
];
