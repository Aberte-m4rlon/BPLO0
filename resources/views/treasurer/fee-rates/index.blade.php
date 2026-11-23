@extends('layouts.app')

@section('title', 'Fee Rates')

@section('content')
    <div class="page-header">
        <div class="page-header-copy bplo-page-header-copy">
            <p class="bplo-eyebrow">Municipal Treasurer's Office</p>
            <h1>Fee Rates</h1>
            <p>Maintain effective peso amounts and schedules applied to permit fee assessments.</p>
        </div>
        <div class="bplo-header-actions">
            <a href="{{ route('treasurer.fee-rates.create') }}" class="btn">New Fee Rate</a>
        </div>
    </div>

    <div class="card">
        <div class="bplo-card-intro">
            <div>
                <h2 class="section">Fee Rate Schedule</h2>
                <p class="muted mb-0">Configured amounts by fee type and effective start date.</p>
            </div>
            <span class="bplo-result-count">{{ $feeRates->total() }} {{ \Illuminate\Support\Str::plural('rate', $feeRates->total()) }}</span>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Fee Type</th><th>Label</th><th>Amount</th><th>Status</th><th>Effective From</th><th>Action</th></tr>
                </thead>
                <tbody>
                    @foreach ($feeRates as $rate)
                        <tr>
                            <td>{{ $rate->feeType?->name }}</td>
                            <td>{{ $rate->label }}</td>
                            <td>&#8369;{{ number_format((float) $rate->amount, 2) }}</td>
                            <td>
                                <span class="badge {{ $rate->is_active ? 'badge-green' : 'badge-gray' }}">
                                    {{ $rate->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $rate->effective_from?->format('M d, Y') ?? '—' }}</td>
                            <td><a href="{{ route('treasurer.fee-rates.edit', $rate) }}" class="btn btn-sm btn-secondary">Edit</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-pagination :paginator="$feeRates" />
    </div>
@endsection
