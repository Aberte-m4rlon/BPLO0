@extends('layouts.app')

@section('title', 'New Fee Rate')

@section('content')
    <div class="page-header">
        <div class="page-header-copy bplo-page-header-copy">
            <p class="bplo-eyebrow">Municipal Treasurer's Office</p>
            <h1>New Fee Rate</h1>
            <p>Add a municipal fee schedule entry for permit fee collections.</p>
        </div>
        <div class="bplo-header-actions">
            <a href="{{ route('treasurer.fee-rates.index') }}" class="btn btn-secondary">Back to Fee Rates</a>
        </div>
    </div>
    <div class="card bplo-form-card bplo-form-card-narrow">
        <h2 class="section">Fee Rate Schedule</h2>
        <p class="bplo-form-intro">Select the fee category and set its amount, calculation type, and effective date.</p>
        <form method="POST" action="{{ route('treasurer.fee-rates.store') }}">
            @csrf
            @include('treasurer.fee-rates._form', ['feeRate' => null])
            <div class="bplo-form-actions">
                <button type="submit" class="btn">Create Fee Rate</button>
                <a href="{{ route('treasurer.fee-rates.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
