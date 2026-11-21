@extends('layouts.app')

@section('title', 'New Fee Type')

@section('content')
    <div class="page-header">
        <div class="page-header-copy bplo-page-header-copy">
            <p class="bplo-eyebrow">System Administration</p>
            <h1>New Fee Type</h1>
            <p>Create a municipal fee category for permit assessments.</p>
        </div>
        <div class="bplo-header-actions">
            <a href="{{ route('admin.fee-types.index') }}" class="btn btn-secondary">Back to Fee Types</a>
        </div>
    </div>
    <div class="card bplo-form-card bplo-form-card-narrow">
        <h2 class="section">Fee Category Details</h2>
        <p class="bplo-form-intro">Enter the category name, unique slug code, active status, and display order.</p>
        <form method="POST" action="{{ route('admin.fee-types.store') }}">
            @csrf
            @include('admin.fee-types._form', ['feeType' => null])
            <div class="bplo-form-actions">
                <button type="submit" class="btn">Create Fee Type</button>
                <a href="{{ route('admin.fee-types.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
