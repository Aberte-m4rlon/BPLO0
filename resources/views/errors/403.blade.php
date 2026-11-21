@extends('layouts.guest')

@section('title', 'Access Not Allowed — '.config('permit.municipality'))

@section('content')
    <div class="card bplo-error-card">
        <p class="bplo-eyebrow">Access Restricted</p>
        <h1>Access Not Allowed</h1>
        <p class="muted">
            Your account doesn't have permission to open this page.
            @auth
                You are currently signed in as <strong>{{ auth()->user()->email }}</strong>
                ({{ strtoupper(auth()->user()->role) }}).
            @endauth
        </p>

        <div class="bplo-error-actions">
            @auth
                <a href="{{ url(auth()->user()->homePath()) }}" class="btn">Go to My Dashboard</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary">Log Out &amp; Switch Account</button>
                </form>
            @else
                <a href="{{ url('/login') }}" class="btn">Sign In</a>
                <a href="{{ url('/') }}" class="btn btn-secondary">Back to Home</a>
            @endauth
        </div>
    </div>
@endsection
