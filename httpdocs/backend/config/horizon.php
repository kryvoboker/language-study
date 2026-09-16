<?php
return [
    'use' => 'default',
    'prefix' => env('HORIZON_PREFIX', 'nativelens_horizon:'),
    'middleware' => ['web'],
    'waits' => ['redis:translations' => 15],
    'environments' => [
        'production' => ['translations' => ['connection' => 'redis', 'queue' => ['translations'], 'balance' => 'auto', 'maxProcesses' => 20, 'tries' => 2, 'timeout' => 180]],
        'local' => ['translations' => ['connection' => 'redis', 'queue' => ['translations'], 'balance' => 'simple', 'maxProcesses' => 4, 'tries' => 1, 'timeout' => 180]],
    ],
];
