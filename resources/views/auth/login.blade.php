<x-guest-layout>
    {{-- LOGO --}}
    <div class="brand-row">
        <img class="brand-logo" src="{{ asset('images/logo.png') }}" alt="Logo" onerror="this.style.display='none';">
    </div>

    <h1 class="auth-title">Log in.</h1>
    <p class="auth-subtitle">
        Log in with your data that you entered during your registration.
    </p>

    @if (session('status'))
        <div class="alert alert-success py-2 small mb-3 rounded-3">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger py-2 small mb-3 rounded-3">
            <div class="fw-semibold mb-1">Ada yang salah:</div>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Enter your email address</label>
            <input
                type="email"
                name="email"
                class="form-control"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                placeholder="name@example.com"
            >
        </div>

        <div class="mb-2">
            <label class="form-label">Enter your password</label>
            <input
                type="password"
                name="password"
                class="form-control"
                required
                autocomplete="current-password"
                placeholder="at least 8 characters"
            >
        </div>

        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                <label class="form-check-label small" for="remember_me">Remember me</label>
            </div>

            @if (Route::has('password.request'))
                <a class="link-brand small" href="{{ route('password.request') }}">Forget password?</a>
            @endif
        </div>

        <button type="submit" class="btn btn-brand w-100">
            Log in
        </button>

       
    </form>
</x-guest-layout>
