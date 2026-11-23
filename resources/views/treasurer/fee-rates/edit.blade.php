@extends('layouts.app')

@section('title', 'Edit Fee Rate')

@section('content')
    <div class="page-header">
        <div class="page-header-copy bplo-page-header-copy">
            <p class="bplo-eyebrow">Municipal Treasurer's Office</p>
            <h1>Edit Fee Rate</h1>
            <p>Update schedule amount and effective status for <strong>{{ $feeRate->label }}</strong>.</p>
        </div>
        <div class="bplo-header-actions">
            <a href="{{ route('treasurer.fee-rates.index') }}" class="btn btn-secondary">Back to Fee Rates</a>
        </div>
    </div>
    <div class="card bplo-form-card bplo-form-card-narrow">
        <h2 class="section">Fee Rate Schedule</h2>
        <p class="bplo-form-intro">Adjust the peso amount, calculation type, active state, or effective start date.</p>
        <form method="POST" action="{{ route('treasurer.fee-rates.update', $feeRate) }}">
            @csrf
            @method('PUT')
            @include('treasurer.fee-rates._form', ['feeRate' => $feeRate])
            <div class="bplo-form-actions">
                <button type="submit" class="btn">Save Changes</button>
                <a href="{{ route('treasurer.fee-rates.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
@endsection
