<?php

declare(strict_types=1);

return [
    'telegram_token' => (string)env('MONOLOG_TELEGRAM_BOT_API_KEY', ''),
    'user_id' => (int)env('MONOLOG_TELEGRAM_CHAT_ID', 0),
];