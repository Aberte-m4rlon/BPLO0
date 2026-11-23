@extends('layouts.guest')

@section('title', 'Verify Permit — '.config('permit.municipality'))

@section('content')
    <div class="bplo-verify-wrap">
        <div class="card">
            <p class="bplo-eyebrow">Public Verification</p>
            <h1>Verify a Permit</h1>
            <p class="bplo-form-intro">
                Scan the QR code on the Mayor's Permit with your phone camera. If you can't scan it,
                type or paste the verification link or code printed at the bottom of the permit.
            </p>

            @isset($invalid)
                <div class="alert alert-warning">Please enter a valid verification code or link.</div>
            @endisset

            <form method="GET" action="{{ route('verify.lookup') }}">
                <div class="form-group">
                    <label for="code">Verification Code or Link</label>
                    <input type="text" id="code" name="code" value="{{ old('code', request('code')) }}"
                           placeholder="{{ url('/verify') }}/..." autocomplete="off" required>
                    @error('code') <div class="field-error">{{ $message }}</div> @enderror
                </div>
                <div class="bplo-form-actions">
                    <button type="submit" class="btn">Verify Permit</button>
                    <a href="{{ url('/') }}" class="btn btn-secondary">Back to {{ config('permit.municipality') }}</a>
                </div>
            </form>
        </div>
    </div>
@endsection
