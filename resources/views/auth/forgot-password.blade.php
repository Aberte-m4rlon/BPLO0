@extends('layouts.guest')

@section('title', 'Forgot Password — '.config('permit.municipality'))

@section('content')
    <div class="bplo-auth-single">
        <div class="auth-card">
            <div class="bplo-auth-card-heading">
                <div class="bplo-auth-lock" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
                <p class="bplo-eyebrow">Account Recovery</p>
                <h2>Forgot Password</h2>
                <p>Enter your registered email address and we'll send a password reset link.</p>
            </div>

            @if (session('status'))
                <div class="alert alert-info">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ url('/forgot-password') }}">
                @csrf
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="name@example.com" required autofocus>
                    @error('email') <div class="field-error">{{ $message }}</div> @enderror
                </div>
                <button type="submit" class="btn btn-block">Send Reset Link</button>
            </form>

            <div class="bplo-auth-note">
                <span>&#9679;</span>
                <a href="{{ url('/login') }}">Back to Sign In</a>
            </div>
        </div>
    </div>
@endsection
