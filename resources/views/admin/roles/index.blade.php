@extends('layouts.admin')

@section('title', 'Roles & Permissions')
@section('breadcrumb', 'Roles & Permissions')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Roles &amp; Permissions</h1>
            <p class="mt-1 text-gray-600">Control which admin menus each role of admin user can open.</p>
        </div>
        <a href="{{ route('admin.roles.create') }}"
           class="bg-[#00BDE0] text-white px-4 py-2 rounded-lg hover:bg-[#00A5C7] transition-colors font-medium">
            Add New Role
        </a>
    </div>

    <!-- Roles Table -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Menu Access</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admins</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($roles as $role)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $role->name }}
                                    @if($role->is_super_admin)
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            Full access
                                        </span>
                                    @endif
                                </div>
                                @if($role->description)
                                    <div class="text-sm text-gray-500">{{ $role->description }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($role->is_super_admin)
                                    <span class="text-sm text-gray-600">All menus</span>
                                @elseif(count($role->permissionKeys()) === 0)
                                    <span class="text-sm text-gray-400">Dashboard only</span>
                                @else
                                    <div class="flex flex-wrap gap-1 max-w-xl">
                                        @foreach($role->permissionKeys() as $key)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                                {{ \App\Support\AdminMenu::label($key) }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $role->users_count }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if($role->is_super_admin)
                                    <span class="text-gray-400">Locked</span>
                                @else
                                    <div class="flex items-center justify-end space-x-3">
                                        <a href="{{ route('admin.roles.edit', $role) }}" class="text-[#00BDE0] hover:text-[#00A5C7]">Edit</a>
                                        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}"
                                              onsubmit="return confirm('Delete the {{ $role->name }} role?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">
                                No roles yet. Create one to start limiting admin menu access.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-800">
        Every admin can always open the Dashboard. All other menus are hidden and blocked unless the assigned role grants them.
    </div>
</div>
@endsection
