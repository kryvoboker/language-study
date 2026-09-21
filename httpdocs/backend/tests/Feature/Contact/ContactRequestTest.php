<?php

declare(strict_types=1);

namespace Tests\Feature\Contact;

use App\Enums\ContactRequestStatus;
use App\Jobs\SendContactRequestToTelegram;
use App\Models\ContactRequest;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class ContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_a_contact_request_with_private_jpeg_attachment(): void
    {
        Queue::fake();
        Storage::fake('local');

        $this->post('/api/v1/contact-requests', [
            'first_name' => 'Guest',
            'last_name' => 'Sender',
            'email' => 'guest@example.test',
            'message' => 'Please help me.',
            'images' => [UploadedFile::fake()->image('screen.jpg')->size(400)],
        ], ['Accept' => 'application/json'])->assertAccepted();

        $contact_request = ContactRequest::query()->with('attachments')->firstOrFail();
        $this->assertSame('Guest', $contact_request->sender_first_name);
        $this->assertSame('Sender', $contact_request->sender_last_name);
        $this->assertSame('guest@example.test', $contact_request->sender_email);
        $this->assertFalse($contact_request->sender_is_blocked);
        $this->assertSame(ContactRequestStatus::Pending, $contact_request->status);
        $this->assertCount(1, $contact_request->attachments);
        Storage::disk('local')->assertExists($contact_request->attachments[0]->path);
        Queue::assertPushedOn('translations', SendContactRequestToTelegram::class);
    }

    public function test_guest_must_supply_name_surname_and_email(): void
    {
        Queue::fake();

        $this->postJson('/api/v1/contact-requests', ['message' => 'Need help.'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['first_name', 'last_name', 'email']);

        $this->assertDatabaseCount('contact_requests', 0);
        Queue::assertNothingPushed();
    }

    public function test_authenticated_blocked_and_inactive_user_can_submit_with_account_identity(): void
    {
        Queue::fake();
        $user = User::factory()->create([
            'name' => 'Account Name',
            'lastname' => 'Account Surname',
            'email' => 'account@example.test',
            'is_active' => false,
            'is_blocked' => true,
        ]);
        Passport::actingAs($user, ['profile']);

        $this->postJson('/api/v1/contact-requests', ['message' => 'I need account support.'])
            ->assertAccepted();

        $contact_request = ContactRequest::query()->firstOrFail();
        $this->assertSame($user->getKey(), $contact_request->user_id);
        $this->assertSame('Account Name', $contact_request->sender_first_name);
        $this->assertSame('Account Surname', $contact_request->sender_last_name);
        $this->assertSame('account@example.test', $contact_request->sender_email);
        $this->assertTrue($contact_request->sender_is_blocked);
    }

    public function test_active_authenticated_user_can_submit_a_contact_request(): void
    {
        Queue::fake();
        $user = User::factory()->create(['is_active' => true, 'is_blocked' => false]);
        Passport::actingAs($user, ['profile']);

        $this->postJson('/api/v1/contact-requests', ['message' => 'An ordinary support request.'])
            ->assertAccepted();

        $this->assertDatabaseHas('contact_requests', [
            'user_id' => $user->getKey(),
            'sender_is_blocked' => false,
        ]);
    }

    public function test_inactive_unblocked_authenticated_user_can_submit_a_contact_request(): void
    {
        Queue::fake();
        $user = User::factory()->create(['is_active' => false, 'is_blocked' => false]);
        Passport::actingAs($user, ['profile']);

        $this->postJson('/api/v1/contact-requests', ['message' => 'I cannot sign in.'])
            ->assertAccepted();

        $this->assertDatabaseHas('contact_requests', [
            'user_id' => $user->getKey(),
            'sender_is_blocked' => false,
        ]);
    }

    public function test_ten_valid_images_and_an_image_at_the_five_megabyte_limit_are_accepted(): void
    {
        Queue::fake();
        Storage::fake('local');
        $guest_data = [
            'first_name' => 'Guest',
            'last_name' => 'Sender',
            'email' => 'guest@example.test',
            'message' => 'Please help me.',
        ];

        $this->post('/api/v1/contact-requests', [
            ...$guest_data,
            'images' => array_map(
                static fn (int $index): UploadedFile => UploadedFile::fake()->image("screen-{$index}.jpg"),
                range(1, 10),
            ),
        ], ['Accept' => 'application/json'])->assertAccepted();

        $this->post('/api/v1/contact-requests', [
            ...$guest_data,
            'email' => 'guest-two@example.test',
            'images' => [UploadedFile::fake()->image('five-megabytes.jpg')->size(5120)],
        ], ['Accept' => 'application/json'])->assertAccepted();

        $this->assertDatabaseCount('contact_requests', 2);
        $this->assertDatabaseCount('contact_request_attachments', 11);
    }

    public function test_authenticated_user_cannot_override_their_contact_identity(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user, ['profile']);

        $this->postJson('/api/v1/contact-requests', [
            'first_name' => 'Impersonated',
            'last_name' => 'Person',
            'email' => 'imposter@example.test',
            'message' => 'Hello.',
        ])->assertUnprocessable();

        $this->assertDatabaseCount('contact_requests', 0);
    }

    public function test_contact_request_rejects_more_than_ten_files_oversized_files_and_webp(): void
    {
        $payload = [
            'first_name' => 'Guest',
            'last_name' => 'Sender',
            'email' => 'guest@example.test',
            'message' => 'Please help me.',
            'images' => array_fill(0, 11, UploadedFile::fake()->image('screen.jpg')),
        ];

        $this->post('/api/v1/contact-requests', $payload, ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('images');

        $payload['images'] = [UploadedFile::fake()->image('oversize.jpg')->size(5121)];
        $this->post('/api/v1/contact-requests', $payload, ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('images.0');

        $payload['images'] = [UploadedFile::fake()->image('unsupported.webp')];
        $this->post('/api/v1/contact-requests', $payload, ['Accept' => 'application/json'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('images.0');

        $this->assertDatabaseCount('contact_requests', 0);
    }

    public function test_web_session_user_can_submit_with_a_valid_csrf_token(): void
    {
        Queue::fake();
        $user = User::factory()->create();

        $this->actingAs($user, 'web')
            ->withSession(['_token' => 'contact-test-token'])
            ->withHeader('X-CSRF-TOKEN', 'contact-test-token')
            ->postJson('/api/v1/contact-requests', ['message' => 'Session request.'])
            ->assertAccepted();

        $this->assertDatabaseHas('contact_requests', ['user_id' => $user->getKey()]);
    }

    public function test_contact_request_is_not_saved_or_queued_when_attachment_storage_fails(): void
    {
        Queue::fake();
        $disk = Mockery::mock();
        $disk->shouldReceive('putFile')->once()->andThrow(new RuntimeException('Storage is unavailable.'));
        Storage::shouldReceive('disk')->with('local')->once()->andReturn($disk);

        $this->post('/api/v1/contact-requests', [
            'first_name' => 'Guest',
            'last_name' => 'Sender',
            'email' => 'guest@example.test',
            'message' => 'Please help me.',
            'images' => [UploadedFile::fake()->image('screen.jpg')],
        ], ['Accept' => 'application/json'])->assertInternalServerError();

        $this->assertDatabaseCount('contact_requests', 0);
        Queue::assertNothingPushed();
    }

    public function test_contact_requests_are_rate_limited(): void
    {
        Queue::fake();
        $payload = [
            'first_name' => 'Guest',
            'last_name' => 'Sender',
            'email' => 'guest@example.test',
            'message' => 'Please help me.',
        ];

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->postJson('/api/v1/contact-requests', $payload)->assertAccepted();
        }

        $this->postJson('/api/v1/contact-requests', $payload)->assertTooManyRequests();
        $this->assertDatabaseCount('contact_requests', 5);
    }
}
