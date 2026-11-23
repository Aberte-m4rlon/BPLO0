@props([
    'title' => 'Admin Dashboard',
    'workspaceKicker' => 'ADMINISTRATION WORKSPACE',
])

@extends('layouts.app')

@section('title', $title)

@section('content')
    {{ $slot }}
@endsection
