<?php

declare(strict_types=1);
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('translation_requests', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->char('request_hash', 64)
                ->nullable(false)
                ->index();
            $table->longText('source_text');
            $table->string('source_language', 12);
            $table->string('target_language', 12);
            $table->string('locale', 12)->default('en');
            $table->string('status', 24)->index();
            $table->string('provider_key')->nullable()->index();
            $table->string('provider_operation_id')->nullable()->index();
            $table->json('result')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('translation_requests');
    }
};
