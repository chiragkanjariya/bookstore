{{--
    Admin role picker. A user may hold several roles at once; the menus they
    can open is the union of every role ticked here.
--}}
<label class="block text-sm font-medium text-gray-700 mb-1">
    Admin Roles <span class="text-red-500">*</span>
</label>

@if($adminRoles->isEmpty())
    <p class="text-sm text-gray-500">
        No admin roles exist yet.
        <a href="{{ route('admin.roles.create') }}" class="text-[#00BDE0] hover:underline">Create one first</a>.
    </p>
@else
    <div class="border border-gray-300 rounded-md divide-y divide-gray-200 max-h-64 overflow-y-auto @error('admin_role_ids') border-red-300 @enderror">
        @foreach($adminRoles as $adminRole)
            <label class="flex items-start gap-3 px-3 py-2 {{ $disabled ? 'opacity-60' : 'hover:bg-gray-50 cursor-pointer' }}">
                <input type="checkbox" name="admin_role_ids[]" value="{{ $adminRole->id }}"
                       {{ $selectedRoleIds->contains($adminRole->id) ? 'checked' : '' }}
                       {{ $disabled ? 'disabled' : '' }}
                       class="admin-role-checkbox mt-0.5 h-4 w-4 text-[#00BDE0] focus:ring-[#00BDE0] border-gray-300 rounded">
                <span class="min-w-0">
                    <span class="block text-sm text-gray-900">
                        {{ $adminRole->name }}
                        @if($adminRole->is_super_admin)
                            <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                full access
                            </span>
                        @endif
                    </span>
                    <span class="block text-xs text-gray-500">
                        @if($adminRole->is_super_admin)
                            Every admin menu
                        @elseif(count($adminRole->permissionKeys()) === 0)
                            Dashboard only
                        @else
                            {{ collect($adminRole->permissionKeys())->map(fn ($k) => \App\Support\AdminMenu::label($k))->implode(', ') }}
                        @endif
                    </span>
                </span>
            </label>
        @endforeach
    </div>
@endif

@if($disabled)
    <p class="mt-1 text-xs text-yellow-600">You cannot change your own roles</p>
@else
    <p class="mt-1 text-xs text-gray-500">
        Tick every role this user should have &mdash; access is combined across them.
        <a href="{{ route('admin.roles.index') }}" class="text-[#00BDE0] hover:underline">Manage roles</a>
    </p>
@endif

@error('admin_role_ids')
    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
@enderror
