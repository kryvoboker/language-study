<?php

declare(strict_types=1);

namespace httpdocs\backend\database\migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('ai_provider_id')
				->constrained('ai_provider_settings', 'id')
				->nullOnDelete();
        });
    }

    public function down(): void
    {
		Schema::table('users', function (Blueprint $table): void {
			$table->dropForeign(['ai_provider_id']);
			$table->dropColumn('ai_provider_id');
		});
    }
};