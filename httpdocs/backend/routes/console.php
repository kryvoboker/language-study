<?php

declare(strict_types=1);
use Illuminate\Support\Facades\Schedule;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;

Schedule::command('passport:purge')->daily();

Artisan::command('passport:public-client {--redirect=}', function (ClientRepository $clients): void {
    $name = resolve_string(config('passport.public_client_name'), 'NativeLens Storefront');
    $redirect = resolve_string($this->option('redirect'), resolve_string(config('passport.public_client_redirect_uri'), string_value(config('app.frontend_url')) . '/auth/callback'));
    $client = Passport::client()->newQuery()->where('name', $name)->where('revoked', false)->first();

    if ($client === null) {
        $client = $clients->createAuthorizationCodeGrantClient($name, [$redirect], false);
        $this->info('Created public Passport client: ' . string_value($client->getKey()));
        return;
    }

    $this->info('Public Passport client already exists: ' . string_value($client->getKey()));
});

Artisan::command('passport:personal-client', function (ClientRepository $clients): void {
    $name = resolve_string(config('passport.personal_client_name'), 'NativeLens Storefront Personal');
    $client = Passport::client()->newQuery()
        ->where('name', $name)
        ->where('revoked', false)
        ->get()
        ->first(fn ($client): bool => $client->hasGrantType('personal_access'));

    if ($client === null) {
        $client = $clients->createPersonalAccessGrantClient($name);
        $this->info('Created personal Passport client: ' . string_value($client->getKey()));

        return;
    }

    $this->info('Personal Passport client already exists: ' . string_value($client->getKey()));
});
