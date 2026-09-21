<?php

declare(strict_types=1);

return [
    'providers' => [
        'openai' => [
            'default_model' => env('OPENAI_DEFAULT_MODEL', 'gpt-5-nano'),
        ],
    ],
];
