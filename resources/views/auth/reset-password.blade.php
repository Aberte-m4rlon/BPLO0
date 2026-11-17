@extends('layouts.guest')

@section('title', 'Reset Password — '.config('permit.municipality'))

@section('content')
    <div class="auth-wrap">
        <div class="auth-card">
            <h1>Reset Password</h1>

            <form method="POST" action="{{ url('/reset-password') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $email) }}" required autofocus>
                    @error('email') <div class="field-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="password">New Password</label>
                    <div class="password-field-wrap">
                        <input type="password" id="password" name="password" required>
                        <button type="button" class="password-toggle-btn" data-target="password" aria-label="Show password" aria-pressed="false">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" /><circle cx="12" cy="12" r="3" />
                            </svg>
                        </button>
                    </div>
                    @error('password') <div class="field-error">{{ $message }}</div> @enderror
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm New Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required>
                </div>
                <button type="submit" class="btn btn-block">Reset Password</button>
            </form>
        </div>
    </div>
@endsection
