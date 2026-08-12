@extends('layouts.admin')

@section('title', 'Edit Role')
@section('breadcrumb', 'Edit Role')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center">
        <a href="{{ route('admin.roles.index') }}" class="text-gray-600 hover:text-gray-900 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit {{ $role->name }}</h1>
            <p class="mt-1 text-gray-600">
                {{ $role->users_count ?? $role->users()->count() }} admin user(s) currently use this role
            </p>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white shadow-sm rounded-lg">
        <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="p-6">
            @method('PUT')
            @include('admin.roles._form', ['submitLabel' => 'Update Role'])
        </form>
    </div>
</div>
@endsection
