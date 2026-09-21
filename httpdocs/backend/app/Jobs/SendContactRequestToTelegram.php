<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Contact\Services\ContactTelegramService;
use App\Models\ContactRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendContactRequestToTelegram implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [60, 300];

    public function __construct(public readonly string $contact_request_id)
    {
        $this->onQueue('translations')->afterCommit();
    }

    public function handle(ContactTelegramService $telegram_service): void
    {
        $contact_request = ContactRequest::query()
            ->with('attachments')
            ->findOrFail($this->contact_request_id);

        $telegram_service->deliver($contact_request);
    }

    public function failed(Throwable $exception): void
    {
        ContactRequest::query()
            ->whereKey($this->contact_request_id)
            ->update(['status' => 'failed']);

        Log::channel('stack')->error('Contact request Telegram delivery failed.', [
            'contact_request_id' => $this->contact_request_id,
            'exception_class' => $exception::class,
        ]);
    }
}
