<?php

declare(strict_types=1);
use Illuminate\Support\Facades\Schedule;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;

Schedule::command('passport:purge')->daily();

Artisan::command('passport:public-client {--redirect=}', function (ClientRepository $clients): void {
    $name = (string) env('PASSPORT_PUBLIC_CLIENT_NAME', 'NativeLens Storefront');
    $redirect = (string) ($this->option('redirect') ?: env('PASSPORT_PUBLIC_CLIENT_REDIRECT_URI', config('app.frontend_url') . '/auth/callback'));
    $client = Passport::client()->newQuery()->where('name', $name)->where('revoked', false)->first();

    if ($client === null) {
        $client = $clients->createAuthorizationCodeGrantClient($name, [$redirect], false);
        $this->info('Created public Passport client: ' . $client->getKey());
        return;
    }

    $this->info('Public Passport client already exists: ' . $client->getKey());
});

Artisan::command('passport:personal-client', function (ClientRepository $clients): void {
    $name = (string) env('PASSPORT_PERSONAL_CLIENT_NAME', 'NativeLens Storefront Personal');
    $client = Passport::client()->newQuery()
        ->where('name', $name)
        ->where('revoked', false)
        ->get()
        ->first(fn ($client): bool => $client->hasGrantType('personal_access'));

    if ($client === null) {
        $client = $clients->createPersonalAccessGrantClient($name);
        $this->info('Created personal Passport client: ' . $client->getKey());

        return;
    }

    $this->info('Personal Passport client already exists: ' . $client->getKey());
});
