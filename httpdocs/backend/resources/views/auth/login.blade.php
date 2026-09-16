<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Sign in</title></head>
<body>
<main>
    <h1>Sign in</h1>
    @if ($errors->any())
        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    @endif
    <form method="post" action="{{ route('login.store') }}">
        @csrf
        <label>Email <input name="email" type="email" value="{{ old('email') }}" required autofocus></label>
        <label>Password <input name="password" type="password" required></label>
        <button type="submit">Sign in</button>
    </form>
</main>
</body>
</html>
