<?php

declare(strict_types=1);

namespace Tests\Feature\Contact;

use App\Domain\Contact\Contracts\ContactTelegramGateway;
use App\Domain\Contact\Services\ContactTelegramService;
use App\Enums\ContactRequestStatus;
use App\Jobs\SendContactRequestToTelegram;
use App\Models\ContactRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class ContactTelegramTest extends TestCase
{
    use RefreshDatabase;

    public function test_telegram_message_escapes_user_html_and_marks_blocked_sender(): void
    {
        $gateway = new FakeContactTelegramGateway();
        $contact_request = ContactRequest::factory()->create([
            'sender_first_name' => '<b>Blocked</b>',
            'sender_email' => 'user&example@example.test',
            'sender_is_blocked' => true,
            'message' => 'Привіт, please <script>alert(1)</script> help.',
        ]);

        (new ContactTelegramService($gateway))->deliver($contact_request);

        $this->assertStringContainsString('Sender account is blocked.', $gateway->messages[0]);
        $this->assertStringContainsString('&lt;b&gt;Blocked&lt;/b&gt;', $gateway->messages[0]);
        $this->assertStringContainsString('&amp;', $gateway->messages[0]);
        $this->assertStringContainsString('&lt;script&gt;', $gateway->messages[1]);
        $this->assertStringContainsString('Привіт, please', $gateway->messages[1]);
        $this->assertSame(ContactRequestStatus::Sent, $contact_request->fresh()->status);
    }

    public function test_telegram_message_content_is_chunked_to_safe_html_lengths(): void
    {
        $gateway = new FakeContactTelegramGateway();
        $contact_request = ContactRequest::factory()->create([
            'message' => str_repeat('<', 2000),
        ]);

        (new ContactTelegramService($gateway))->deliver($contact_request);

        $this->assertGreaterThan(2, count($gateway->messages));

        foreach (array_slice($gateway->messages, 1) as $message) {
            $this->assertLessThanOrEqual(4096, mb_strlen($message));
        }
    }

    public function test_missing_telegram_configuration_keeps_request_pending(): void
    {
        $gateway = new FakeContactTelegramGateway(configured: false);
        $contact_request = ContactRequest::factory()->create();

        try {
            (new ContactTelegramService($gateway))->deliver($contact_request);
            $this->fail('Missing Telegram configuration must be retried by the queue.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Telegram contact delivery is not configured.', $exception->getMessage());
        }

        $this->assertSame([], $gateway->messages);
        $this->assertSame(ContactRequestStatus::Pending, $contact_request->fresh()->status);
    }

    public function test_telegram_delivery_includes_privately_stored_photos(): void
    {
        Storage::fake('local');
        $gateway = new FakeContactTelegramGateway();
        $contact_request = ContactRequest::factory()->create();
        $path = 'contact-requests/' . $contact_request->getKey() . '/image.jpg';
        Storage::disk('local')->put($path, 'fake image bytes');
        $contact_request->attachments()->create([
            'disk' => 'local',
            'path' => $path,
            'mime_type' => 'image/jpeg',
            'size_bytes' => 16,
            'position' => 0,
        ]);

        (new ContactTelegramService($gateway))->deliver($contact_request->load('attachments'));

        $this->assertCount(1, $gateway->photo_calls);
        $this->assertCount(1, $gateway->photo_calls[0]);
        $this->assertSame(ContactRequestStatus::Sent, $contact_request->fresh()->status);
    }

    public function test_final_queue_failure_marks_request_failed_and_job_retries_transient_errors(): void
    {
        $contact_request = ContactRequest::factory()->create();
        $job = new SendContactRequestToTelegram((string) $contact_request->getKey());

        $this->assertSame(3, $job->tries);
        $this->assertSame([60, 300], $job->backoff);

        $job->failed(new RuntimeException('Telegram delivery failed.'));

        $this->assertSame(ContactRequestStatus::Failed, $contact_request->fresh()->status);
    }
}

class FakeContactTelegramGateway implements ContactTelegramGateway
{
    /** @var list<string> */
    public array $messages = [];

    /** @var list<list<string>> */
    public array $photo_calls = [];

    public function __construct(private readonly bool $configured = true)
    {
    }

    public function isConfigured(): bool
    {
        return $this->configured;
    }

    public function sendMessage(string $html): void
    {
        $this->messages[] = $html;
    }

    /** @param list<string> $paths */
    public function sendPhotos(array $paths): void
    {
        $this->photo_calls[] = $paths;
    }
}
