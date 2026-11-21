@extends('layouts.app')

@section('title', 'Fee Types')

@section('content')
    <div class="page-header">
        <div class="page-header-copy bplo-page-header-copy">
            <p class="bplo-eyebrow">System Administration</p>
            <h1>Fee Types</h1>
            <p>Define the municipal assessment categories used when calculating business permit fees.</p>
        </div>
        <div class="bplo-header-actions">
            <a href="{{ route('admin.fee-types.create') }}" class="btn">New Fee Type</a>
        </div>
    </div>

    <div class="card">
        <div class="bplo-card-intro">
            <div>
                <h2 class="section">Assessment Categories</h2>
                <p class="muted mb-0">Fee categories linked to active rate schedules in BPLO assessment.</p>
            </div>
            <span class="bplo-result-count">{{ $feeTypes->total() }} {{ \Illuminate\Support\Str::plural('category', $feeTypes->total()) }}</span>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Name</th><th>Code</th><th>Status</th><th>Order</th><th>Action</th></tr>
                </thead>
                <tbody>
                    @foreach ($feeTypes as $feeType)
                        <tr>
                            <td>{{ $feeType->name }}</td>
                            <td><code>{{ $feeType->code }}</code></td>
                            <td>
                                <span class="badge {{ $feeType->is_active ? 'badge-green' : 'badge-gray' }}">
                                    {{ $feeType->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $feeType->sort_order }}</td>
                            <td><a href="{{ route('admin.fee-types.edit', $feeType) }}" class="btn btn-sm btn-secondary">Edit</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-pagination :paginator="$feeTypes" />
    </div>
@endsection
