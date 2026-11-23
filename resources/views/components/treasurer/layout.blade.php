@props([
    'title' => 'Treasurer Dashboard',
])

@extends('layouts.app')

@section('title', $title)

@section('content')
    {{ $slot }}
@endsection
