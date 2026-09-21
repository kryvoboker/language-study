<?php

declare(strict_types=1);

namespace App\Domain\Contact\Services;

use App\Domain\Contact\Contracts\ContactTelegramGateway;
use App\Models\ContactRequest;
use App\Models\ContactRequestAttachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

readonly class ContactTelegramService
{
    public function __construct(private ContactTelegramGateway $gateway)
    {
    }

    public function deliver(ContactRequest $contact_request): void
    {
        if (! $this->gateway->isConfigured()) {
            throw new RuntimeException('Telegram contact delivery is not configured.');
        }

        $paths = array_values($contact_request->attachments
            ->map(fn (ContactRequestAttachment $attachment): string => Storage::disk($attachment->disk)->path($attachment->path))
            ->all());

        if ($paths !== []) {
            foreach ($paths as $path) {
                if (! is_file($path)) {
                    throw new RuntimeException('A contact attachment is missing from private storage.');
                }
            }
        }

        foreach ($this->formatMessages($contact_request) as $message) {
            $this->gateway->sendMessage($message);
        }

        if ($paths !== []) {
            $this->gateway->sendPhotos($paths);
        }

        $contact_request->forceFill([
            'status' => 'sent',
            'sent_at' => now(),
        ])->save();
    }

    /** @return list<string> */
    private function formatMessages(ContactRequest $contact_request): array
    {
        $sender_name = Str::trim(implode(' ', array_filter([
            $contact_request->sender_first_name,
            $contact_request->sender_last_name,
        ])));
        $safe_values = [
            '<b>Contact request</b>',
            '<b>Request ID:</b> ' . e(string_value($contact_request->getKey())),
            '<b>From:</b> ' . e($sender_name),
            '<b>Email:</b> ' . e($contact_request->sender_email),
        ];

        if ($contact_request->sender_is_blocked) {
            $safe_values[] = '<b>Sender account is blocked.</b>';
        }

        $messages = [implode("\n", $safe_values)];

        foreach (mb_str_split($contact_request->message, 650) as $index => $chunk) {
            $prefix = $index === 0 ? '<b>Message:</b> ' : '<b>Message (continued):</b> ';
            $messages[] = $prefix . e($chunk);
        }

        return $messages;
    }
}