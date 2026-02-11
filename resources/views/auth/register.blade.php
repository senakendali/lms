<x-guest-layout>
    {{-- LOGO --}}
    <div class="brand-row">
        <img class="brand-logo" src="{{ asset('images/logo.png') }}" alt="Logo" onerror="this.style.display='none';">
    </div>

    <h1 class="auth-title">Register.</h1>
    <p class="auth-subtitle">
        Create your account to start learning.
    </p>

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

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Full name</label>
            <input
                type="text"
                name="name"
                class="form-control"
                value="{{ old('name') }}"
                required
                autofocus
                autocomplete="name"
                placeholder="Your name"
            >
        </div>

        <div class="mb-3">
            <label class="form-label">Email address</label>
            <input
                type="email"
                name="email"
                class="form-control"
                value="{{ old('email') }}"
                required
                autocomplete="username"
                placeholder="name@example.com"
            >
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input
                type="password"
                name="password"
                class="form-control"
                required
                autocomplete="new-password"
                placeholder="at least 8 characters"
            >
        </div>

        <div class="mb-3">
            <label class="form-label">Confirm password</label>
            <input
                type="password"
                name="password_confirmation"
                class="form-control"
                required
                autocomplete="new-password"
                placeholder="repeat your password"
            >
        </div>

        <button type="submit" class="btn btn-brand w-100">
            Create account
        </button>

        <div class="text-center mt-3 small text-muted">
            Already have an account?
            <a class="link-brand" href="{{ route('login') }}">Log in</a>
        </div>
    </form>
</x-guest-layout>
