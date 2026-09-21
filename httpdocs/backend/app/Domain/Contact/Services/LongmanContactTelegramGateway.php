<?php

declare(strict_types=1);

namespace App\Domain\Contact\Services;

use App\Domain\Contact\Contracts\ContactTelegramGateway;
use Longman\TelegramBot\Entities\InputMedia\InputMediaPhoto;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use RuntimeException;
use Throwable;

class LongmanContactTelegramGateway implements ContactTelegramGateway
{
    public function isConfigured(): bool
    {
        return filled(config('contact.telegram.bot_token')) && filled(config('contact.telegram.chat_id'));
    }

    public function sendMessage(string $html): void
    {
        $this->initialize();
		try {
			$response = Request::sendMessage([
				'chat_id' => string_value(config('contact.telegram.chat_id')),
			    'text' => $html,
			    'parse_mode' => 'HTML',
			    'disable_web_page_preview' => true,
			]);
		} catch (Throwable $e) {
			throw new RuntimeException('Telegram contact message delivery failed.' . PHP_EOL . "Error message: " . $e->getMessage());
		}

        if (! $response->isOk()) {
            throw new RuntimeException('Telegram rejected the contact message.');
        }
    }

    /** @param list<string> $paths */
    public function sendPhotos(array $paths): void
    {
        $this->initialize();
        $chat_id = string_value(config('contact.telegram.chat_id'));

        try {
            try {
                if (count($paths) === 1) {
                    $response = Request::sendPhoto([
                        'chat_id' => $chat_id,
                        'photo' => Request::encodeFile($paths[0]),
                    ]);
                } else {
                    $media = array_map(
                        static fn (string $path): InputMediaPhoto => new InputMediaPhoto([
                            'media' => Request::encodeFile($path),
                        ]),
                        $paths,
                    );
                    $response = Request::sendMediaGroup(['chat_id' => $chat_id, 'media' => $media]);
                }
            } catch (Throwable) {
                throw new RuntimeException('Telegram contact attachment delivery failed.');
            }
        } catch (Throwable) {
            throw new RuntimeException('Telegram contact attachment delivery failed.');
        }

        if (! $response->isOk()) {
            throw new RuntimeException('Telegram rejected contact attachments.');
        }
    }

    public function initialize(): void
    {
        $token = string_value(config('contact.telegram.bot_token'));

        if (! $this->isConfigured()) {
            throw new RuntimeException('Telegram contact delivery is not configured.');
        }

        try {
            try {
                new Telegram($token);
            } catch (Throwable) {
                throw new RuntimeException('Telegram contact delivery could not be initialized.');
            }
        } catch (Throwable) {
            throw new RuntimeException('Telegram contact delivery could not be initialized.');
        }
    }
}