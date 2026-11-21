@extends('layouts.app')

@section('title', 'Edit Application')

@section('content')
    <div class="page-header">
        <div class="page-header-copy bplo-page-header-copy">
            <p class="bplo-eyebrow">Applicant Portal</p>
            <h1>Edit Application</h1>
            <p>
                {{ $application->business_name ?? 'Draft Application' }}
                @if ($application->tracking_number)
                    · Tracking <strong>{{ $application->tracking_number }}</strong>
                @endif
            </p>
        </div>
        <div class="bplo-header-actions">
            <a href="{{ route('applications.show', $application) }}" class="btn btn-secondary">View Status</a>
            <a href="{{ route('applications.summary', $application) }}" class="btn">Review &amp; Submit</a>
        </div>
    </div>

    @if ($application->status === \App\Models\Application::STATUS_FOR_CORRECTION)
        <div class="alert alert-warning">
            This application was returned for correction. Please review the BPLO remarks on the
            <a href="{{ route('applications.show', $application) }}">status page</a>, update the flagged items, and resubmit.
        </div>
    @endif

    <div class="card bplo-form-card">
        <h2 class="section">Business Details</h2>
        <p class="bplo-form-intro">Keep your applicant and business profile accurate before proceeding to submission.</p>
        <form method="POST" action="{{ route('applications.update', $application) }}">
            @csrf
            @method('PUT')
            @include('applicant._form-fields', ['application' => $application])
            <div class="bplo-form-actions">
                <button type="submit" class="btn">Save Changes</button>
                <a href="{{ route('applications.summary', $application) }}" class="btn btn-secondary">Proceed to Review</a>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="bplo-card-intro">
            <div>
                <h2 class="section">Requirements Checklist &amp; Uploads</h2>
                <p class="muted mb-0">
                    Choose a file for each document, then click <strong>Upload All Requirements</strong> once.
                    Allowed: {{ implode(', ', (array) config('permit.uploads.mimes')) }}. Max {{ round(config('permit.uploads.max_kb') / 1024, 1) }} MB each.
                </p>
            </div>
        </div>

        {{-- One form for every document: pick files, upload them all together. --}}
        <form method="POST" action="{{ route('applications.requirements.uploadMany', $application) }}" enctype="multipart/form-data">
            @csrf
            <div class="table-wrap">
                <table class="data">
                    <thead>
                        <tr>
                            <th>Document</th>
                            <th>Required</th>
                            <th>Status</th>
                            <th>Uploaded File</th>
                            <th>Choose File</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requirementTypes as $type)
                            @php($req = $application->requirements->firstWhere('requirement_type_id', $type->id))
                            <tr>
                                <td>
                                    {{ $type->name }}
                                    @if ($req && $req->correction_note)
                                        <div class="field-error">Note: {{ $req->correction_note }}</div>
                                    @endif
                                </td>
                                <td>{{ $type->is_required ? 'Yes' : 'Optional' }}</td>
                                <td>
                                    @if ($req)
                                        <span class="badge {{ $req->checklist_status === 'COMPLETE' ? 'badge-green' : ($req->checklist_status === 'FOR_CORRECTION' ? 'badge-yellow' : 'badge-gray') }}">
                                            {{ ucwords(strtolower(str_replace('_', ' ', $req->checklist_status))) }}
                                        </span>
                                    @else
                                        <span class="badge badge-gray">Missing</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($req && $req->stored_path)
                                        <a href="{{ route('requirements.file', $req) }}">{{ $req->original_filename }}</a>
                                    @else
                                        <span class="muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <input type="file" name="files[{{ $type->id }}]"
                                           accept="{{ collect((array) config('permit.uploads.mimes'))->map(fn ($m) => '.'.$m)->implode(',') }}">
                                    @error('files.'.$type->id) <div class="field-error">{{ $message }}</div> @enderror
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @error('files') <div class="field-error" style="margin-top:10px">{{ $message }}</div> @enderror

            <div class="bplo-form-actions">
                <button type="submit" class="btn btn-success">Upload All Requirements</button>
                <span class="muted">You can upload now and add or replace files later.</span>
            </div>
        </form>

        {{-- Remove buttons are separate (a form can't nest inside the upload form). --}}
        @php($uploaded = $application->requirements->filter(fn ($r) => $r->stored_path))
        @if ($uploaded->isNotEmpty())
            <h3 class="bplo-subcard-section-title">Remove an Uploaded File</h3>
            <div class="bplo-chip-list">
                @foreach ($requirementTypes as $type)
                    @php($req = $application->requirements->firstWhere('requirement_type_id', $type->id))
                    @if ($req && $req->stored_path)
                        <form method="POST" action="{{ route('applications.requirements.remove', [$application, $type]) }}"
                              onsubmit="return confirm('Remove the uploaded {{ $type->name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Remove {{ $type->name }}</button>
                        </form>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
@endsection
