<?php

return [
	'providers' => [
		'openai' => [
			'default_model' => env('OPENAI_DEFAULT_MODEL', 'gpt-5-nano')
		],
	],
];