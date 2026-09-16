<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Authorize {{ $client->name }}</title></head>
<body>
<main>
    <h1>Authorize {{ $client->name }}</h1>
    <p>{{ $client->name }} is requesting access to your account.</p>
    @if (count($scopes) > 0)
        <ul>@foreach ($scopes as $scope)<li>{{ $scope->description }}</li>@endforeach</ul>
    @endif
    <form method="post" action="{{ route('passport.authorizations.approve') }}">
        @csrf
        <input type="hidden" name="auth_token" value="{{ $authToken }}">
        <button type="submit">Authorize</button>
    </form>
    <form method="post" action="{{ route('passport.authorizations.deny') }}">
        @csrf
        @method('delete')
        <input type="hidden" name="auth_token" value="{{ $authToken }}">
        <button type="submit">Deny</button>
    </form>
</main>
</body>
</html>
