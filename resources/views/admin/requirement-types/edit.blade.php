@extends('layouts.app')

@section('title', 'Edit Requirement Type')

@section('content')
    <div class="page-header">
        <div class="page-header-copy bplo-page-header-copy">
            <p class="bplo-eyebrow">System Administration</p>
            <h1>Edit Requirement Type</h1>
            <p>Update checklist settings for <strong>{{ $type->name }}</strong>.</p>
        </div>
        <div class="bplo-header-actions">
            <a href="{{ route('admin.requirement-types.index') }}" class="btn btn-secondary">Back to Requirement Types</a>
        </div>
    </div>
    <div class="card bplo-form-card bplo-form-card-narrow">
        <h2 class="section">Requirement Configuration</h2>
        <p class="bplo-form-intro">Modify the label, required flag, activation state, or checklist order.</p>
        <form method="POST" action="{{ route('admin.requirement-types.update', $type) }}">
            @csrf
            @method('PUT')
            @include('admin.requirement-types._form', ['type' => $type])
            <div class="bplo-form-actions">
                <button type="submit" class="btn">Save Changes</button>
                <a href="{{ route('admin.requirement-types.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
