@extends('layouts.app')

@section('title', 'Edit Fee Type')

@section('content')
    <div class="page-header">
        <div class="page-header-copy bplo-page-header-copy">
            <p class="bplo-eyebrow">System Administration</p>
            <h1>Edit Fee Type</h1>
            <p>Modify assessment category settings for <strong>{{ $feeType->name }}</strong>.</p>
        </div>
        <div class="bplo-header-actions">
            <a href="{{ route('admin.fee-types.index') }}" class="btn btn-secondary">Back to Fee Types</a>
        </div>
    </div>
    <div class="card bplo-form-card bplo-form-card-narrow">
        <h2 class="section">Fee Category Details</h2>
        <p class="bplo-form-intro">Update the display name, code slug, availability, or sort order.</p>
        <form method="POST" action="{{ route('admin.fee-types.update', $feeType) }}">
            @csrf
            @method('PUT')
            @include('admin.fee-types._form', ['feeType' => $feeType])
            <div class="bplo-form-actions">
                <button type="submit" class="btn">Save Changes</button>
                <a href="{{ route('admin.fee-types.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
