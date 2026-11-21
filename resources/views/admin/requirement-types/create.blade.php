@extends('layouts.app')

@section('title', 'New Requirement Type')

@section('content')
    <div class="page-header">
        <div class="page-header-copy bplo-page-header-copy">
            <p class="bplo-eyebrow">System Administration</p>
            <h1>New Requirement Type</h1>
            <p>Add a new documentary requirement to the permit application checklist.</p>
        </div>
        <div class="bplo-header-actions">
            <a href="{{ route('admin.requirement-types.index') }}" class="btn btn-secondary">Back to Requirement Types</a>
        </div>
    </div>
    <div class="card bplo-form-card bplo-form-card-narrow">
        <h2 class="section">Requirement Configuration</h2>
        <p class="bplo-form-intro">Set the display name, unique slug code, and whether applicants must upload it.</p>
        <form method="POST" action="{{ route('admin.requirement-types.store') }}">
            @csrf
            @include('admin.requirement-types._form', ['type' => null])
            <div class="bplo-form-actions">
                <button type="submit" class="btn">Create Requirement Type</button>
                <a href="{{ route('admin.requirement-types.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
