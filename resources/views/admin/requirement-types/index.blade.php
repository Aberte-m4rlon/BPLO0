@extends('layouts.app')

@section('title', 'Requirement Types')

@section('content')
    <div class="page-header">
        <div class="page-header-copy bplo-page-header-copy">
            <p class="bplo-eyebrow">System Administration</p>
            <h1>Requirement Types</h1>
            <p>Configure the checklist documents applicants upload with every business permit application.</p>
        </div>
        <div class="bplo-header-actions">
            <a href="{{ route('admin.requirement-types.create') }}" class="btn">New Requirement Type</a>
        </div>
    </div>

    <div class="card">
        <div class="bplo-card-intro">
            <div>
                <h2 class="section">Configured Documents</h2>
                <p class="muted mb-0">Required status, sort order, and active availability in the applicant portal.</p>
            </div>
            <span class="bplo-result-count">{{ $types->total() }} {{ \Illuminate\Support\Str::plural('type', $types->total()) }}</span>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Name</th><th>Code</th><th>Required</th><th>Status</th><th>Order</th><th>Action</th></tr>
                </thead>
                <tbody>
                    @foreach ($types as $type)
                        <tr>
                            <td>{{ $type->name }}</td>
                            <td><code>{{ $type->code }}</code></td>
                            <td>
                                <span class="badge {{ $type->is_required ? 'badge-blue' : 'badge-gray' }}">
                                    {{ $type->is_required ? 'Required' : 'Optional' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $type->is_active ? 'badge-green' : 'badge-gray' }}">
                                    {{ $type->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $type->sort_order }}</td>
                            <td><a href="{{ route('admin.requirement-types.edit', $type) }}" class="btn btn-sm btn-secondary">Edit</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-pagination :paginator="$types" />
    </div>
@endsection
