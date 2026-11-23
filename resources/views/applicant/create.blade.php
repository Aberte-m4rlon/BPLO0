@extends('layouts.app')

@section('title', 'New Application')

@section('content')
    <div class="page-header">
        <div class="page-header-copy bplo-page-header-copy">
            <p class="bplo-eyebrow">Applicant Portal</p>
            <h1>New Business Permit Application</h1>
            <p>Provide your applicant and business details to save an application draft before uploading documents.</p>
        </div>
        <div class="bplo-header-actions">
            <a href="{{ url('/dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>

    <div class="card bplo-form-card">
        <h2 class="section">Application Details</h2>
        <p class="bplo-form-intro">Your registered account details have been auto-filled below. Complete or review your business details to save a draft and upload requirements on the next step.</p>
        <form method="POST" action="{{ url('/applications') }}">
            @csrf
            @include('applicant._form-fields', [
                'application' => null,
                'previousApplication' => $previousApplication ?? null,
                'suggestedBarangay' => $suggestedBarangay ?? null,
            ])
            <div class="bplo-form-actions">
                <button type="submit" class="btn">Save Draft &amp; Continue</button>
                <a href="{{ url('/dashboard') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
