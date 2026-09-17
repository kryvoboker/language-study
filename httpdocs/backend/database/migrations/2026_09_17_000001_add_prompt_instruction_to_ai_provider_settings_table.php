<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('ai_provider_settings', function (Blueprint $table): void {
            $table->longText('prompt_instruction')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('ai_provider_settings', function (Blueprint $table): void {
            $table->dropColumn('prompt_instruction');
        });
    }
};
