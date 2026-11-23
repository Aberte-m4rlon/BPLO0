@extends('layouts.guest')

@section('title', 'Register — '.config('permit.municipality'))

@section('content')
    <div class="auth-wrap bplo-auth-wrap">
        <section class="bplo-auth-intro">
            <p class="bplo-eyebrow">Applicant Self-Service</p>
            <h1>Start your business.<br>Licensed online.</h1>
            <p>Create an applicant account to file new business permit applications, upload requirements, pay assessed fees, and download your QR-verified Mayor's Permit.</p>
            <div class="bplo-auth-feature">
                <span aria-hidden="true">01</span>
                <div><strong>Paperless filing</strong><small>Submit forms and required documents from any device.</small></div>
            </div>
            <div class="bplo-auth-feature">
                <span aria-hidden="true">02</span>
                <div><strong>Real-time status tracking</strong><small>Receive updates at every BPLO &amp; Treasury review milestone.</small></div>
            </div>
        </section>

        <div class="auth-card" style="max-width: 520px;">
            <div class="bplo-auth-card-heading">
                <span class="bplo-auth-lock" aria-hidden="true">BP</span>
                <p class="bplo-eyebrow">Online Registration</p>
                <h2>Create an Account</h2>
                <p class="muted">Register to apply for a municipal business permit.</p>
            </div>

            <form method="POST" action="{{ url('/register') }}">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Juan Dela Cruz" required autofocus>
                        @error('name') <div class="field-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="juan@example.com" required>
                        @error('email') <div class="field-error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="contact_number">Contact Number</label>
                        <input type="text" id="contact_number" name="contact_number" value="{{ old('contact_number') }}" placeholder="09171234567" required>
                        @error('contact_number') <div class="field-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" value="{{ old('address') }}" placeholder="Sitio / Street, Barangay, Bansud" required>
                        @error('address') <div class="field-error">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-field-wrap">
                            <input type="password" id="password" name="password" placeholder="Create password" required>
                            <button type="button" class="password-toggle-btn" data-target="password" aria-label="Show password" aria-pressed="false">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" /><circle cx="12" cy="12" r="3" />
                                </svg>
                            </button>
                        </div>
                        @error('password') <div class="field-error">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">Confirm Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Repeat password" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-block">Create Applicant Account <span aria-hidden="true">→</span></button>
            </form>

            <p class="bplo-auth-note">
                <span aria-hidden="true">●</span>
                Already registered? <a href="{{ url('/login') }}">Sign in here</a>
            </p>
        </div>
    </div>
@endsection
