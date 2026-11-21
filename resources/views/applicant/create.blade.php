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
        <p class="bplo-form-intro">Fill in your business details below to create a draft. You'll upload required supporting documents on the next step.</p>
        <form method="POST" action="{{ url('/applications') }}">
            @csrf
            @include('applicant._form-fields', ['application' => null])
            <div class="bplo-form-actions">
                <button type="submit" class="btn">Save Draft &amp; Continue</button>
                <a href="{{ url('/dashboard') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
