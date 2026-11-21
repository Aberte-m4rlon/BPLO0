@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <div class="page-header">
        <div class="page-header-copy bplo-page-header-copy">
            <p class="bplo-eyebrow">System Administration</p>
            <h1>Users</h1>
            <p>Manage applicant accounts, municipal personnel roles, and account activation status.</p>
        </div>
        <div class="bplo-header-actions">
            <a href="{{ route('admin.users.create') }}" class="btn">New User</a>
        </div>
    </div>

    <div class="card">
        <div class="bplo-card-intro">
            <div>
                <h2 class="section">Registered Accounts</h2>
                <p class="muted mb-0">All applicant and staff accounts with access to the BPLO system.</p>
            </div>
            <span class="bplo-result-count">{{ $users->total() }} {{ \Illuminate\Support\Str::plural('user', $users->total()) }}</span>
        </div>
        <div class="table-wrap">
            <table class="data">
                <thead>
                    <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td><span class="badge badge-blue">{{ ucfirst($user->role) }}</span></td>
                            <td>
                                <span class="badge {{ $user->is_active ? 'badge-green' : 'badge-gray' }}">
                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td><a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-secondary">Edit</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-pagination :paginator="$users" />
    </div>
@endsection
