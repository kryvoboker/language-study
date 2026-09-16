<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AiProviderSetting;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $access = Permission::findOrCreate('access_admin_panel', 'web');
        Role::findOrCreate('user', 'web');
        Role::findOrCreate('admin', 'web')->givePermissionTo($access);
        Role::findOrCreate('super_admin', 'web')->givePermissionTo($access);
        AiProviderSetting::query()->firstOrCreate(['key' => 'openai'], ['name' => 'OpenAI', 'enabled' => false, 'is_default' => true, 'configuration' => ['api_key' => '', 'model' => 'gpt-5.6']]);
    }
}
