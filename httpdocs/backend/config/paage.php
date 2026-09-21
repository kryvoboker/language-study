<?php

return [
	'contact' => [
		'telegram' => [
			'user_id' => (int)env('CONCONTACT_PAGE_TELEGRAM_USER_ID', 0),
			'bot_token' => (string)env('CONTACT_PAGE_TELEGRAM_BOT_API_TOKEN', ''),
		],
	],
];