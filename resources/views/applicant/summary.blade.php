@extends('layouts.app')

@section('title', 'Review & Submit')

@section('content')
    <div class="page-header">
        <div class="page-header-copy bplo-page-header-copy">
            <p class="bplo-eyebrow">Applicant Portal · Step 3 of 3</p>
            <h1>Review &amp; Submit</h1>
            <p>Verify all business details and required document uploads before submitting to BPLO.</p>
        </div>
        <div class="bplo-header-actions">
            <a href="{{ route('applications.edit', $application) }}" class="btn btn-secondary">Back to Edit</a>
        </div>
    </div>

    <div class="bplo-detail-grid">
        <div class="card">
            <h2 class="section">Applicant Information</h2>
            <dl class="kv">
                <dt>Applicant Name</dt><dd>{{ $application->applicant_name }}</dd>
                <dt>Contact Number</dt><dd>{{ $application->contact_number }}</dd>
                <dt>Email</dt><dd>{{ $application->email }}</dd>
                <dt>Ownership Type</dt><dd>{{ $application->ownership_type }}</dd>
                <dt>DTI / SEC</dt><dd>{{ $application->dti_sec_type ? $application->dti_sec_type.' — '.$application->dti_sec_number : '—' }}</dd>
            </dl>
        </div>

        <div class="card">
            <h2 class="section">Business Information</h2>
            <dl class="kv">
                <dt>Business Name</dt><dd>{{ $application->business_name }}</dd>
                <dt>Business Address</dt><dd>{{ $application->business_address }}</dd>
                <dt>Barangay</dt><dd>{{ $application->barangay }}</dd>
                <dt>Nature of Business</dt><dd>{{ $application->business_nature }}</dd>
                <dt>Number of Employees</dt><dd>{{ $application->number_of_employees }}</dd>
            </dl>
        </div>
    </div>

    <div class="card">
        <h2 class="section">Requirements Checklist</h2>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Document</th><th>Required</th><th>Status</th><th>File</th></tr>
                </thead>
                <tbody>
                    @foreach ($requirementTypes as $type)
                        @php($req = $application->requirements->firstWhere('requirement_type_id', $type->id))
                        <tr>
                            <td>{{ $type->name }}</td>
                            <td>{{ $type->is_required ? 'Yes' : 'Optional' }}</td>
                            <td>
                                @if ($req && $req->checklist_status === 'COMPLETE')
                                    <span class="badge badge-green">Complete</span>
                                @elseif ($type->is_required)
                                    <span class="badge badge-red">Missing</span>
                                @else
                                    <span class="badge badge-gray">Optional</span>
                                @endif
                            </td>
                            <td>{{ $req?->original_filename ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="bplo-primary-action-bar">
            <div class="bplo-primary-action-copy">
                <strong>Ready to submit your application?</strong>
                <span>Once you submit, a tracking number is assigned and your application is sent to the BPLO for review.</span>
            </div>
            <form method="POST" action="{{ route('applications.submit', $application) }}">
                @csrf
                <button type="submit" class="btn btn-success">Submit Application</button>
            </form>
        </div>
    </div>
@endsection
