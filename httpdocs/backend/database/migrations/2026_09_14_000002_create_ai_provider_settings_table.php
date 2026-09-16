<?php

declare(strict_types=1);

namespace httpdocs\backend\database\migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('ai_provider_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->boolean('enabled')->default(false);
            $table->boolean('is_default')->default(false);
            $table->longText('configuration');
            $table->timestamps();
        });
        Schema::table('users', function (Blueprint $table): void {
            $table->foreign('ai_provider_id')->references('id')->on('ai_provider_settings')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropForeign(['ai_provider_id']));
        Schema::dropIfExists('ai_provider_settings');
    }
};
