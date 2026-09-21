<?php

declare(strict_types=1);

return [
    'telegram' => [
        'bot_token' => env('CONTACT_PAGE_TELEGRAM_BOT_API_TOKEN'),
        'chat_id' => env('CONTACT_PAGE_TELEGRAM_USER_ID'),
    ],
];