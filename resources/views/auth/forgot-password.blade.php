<x-guest-layout>
    {{-- LOGO --}}
    <div class="brand-row">
        <img class="brand-logo" src="{{ asset('images/logo.png') }}" alt="Logo" onerror="this.style.display='none';">
    </div>

    <h1 class="auth-title">Forgot password.</h1>
    <p class="auth-subtitle">
        No worries. Enter your email and we’ll send you a reset link.
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

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Email address</label>
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

        <button type="submit" class="btn btn-brand w-100">
            Email password reset link
        </button>

        <div class="text-center mt-3 small text-muted">
            Back to <a class="link-brand" href="{{ route('login') }}">Log in</a>
        </div>
    </form>
</x-guest-layout>
