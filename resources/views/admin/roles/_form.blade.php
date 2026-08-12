@csrf

<div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                Role Name <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" id="name" required
                   value="{{ old('name', $role->name) }}"
                   placeholder="e.g. Order Manager"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('name') border-red-300 @enderror">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Description -->
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <input type="text" name="description" id="description"
                   value="{{ old('description', $role->description) }}"
                   placeholder="What this role is for"
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-[#00BDE0] focus:border-[#00BDE0] @error('description') border-red-300 @enderror">
            @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Menu Permissions -->
    <div>
        <div class="flex items-center justify-between mb-3">
            <div>
                <h3 class="text-sm font-medium text-gray-900">Menu Permissions</h3>
                <p class="text-xs text-gray-500">Tick the admin menus this role can open. Dashboard is always available.</p>
            </div>
            <label class="flex items-center text-sm text-gray-700">
                <input type="checkbox" id="select-all-permissions"
                       class="h-4 w-4 text-[#00BDE0] focus:ring-[#00BDE0] border-gray-300 rounded">
                <span class="ml-2">Select all</span>
            </label>
        </div>

        @php($selected = old('permissions', $role->permissionKeys()))

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($menuGroups as $group)
                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">{{ $group['label'] }}</h4>
                    <div class="space-y-2">
                        @foreach($group['items'] as $item)
                            <label class="flex items-center text-sm text-gray-800">
                                <input type="checkbox" name="permissions[]" value="{{ $item['key'] }}"
                                       {{ in_array($item['key'], $selected, true) ? 'checked' : '' }}
                                       class="permission-checkbox h-4 w-4 text-[#00BDE0] focus:ring-[#00BDE0] border-gray-300 rounded">
                                <span class="ml-2">{{ $item['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        @error('permissions')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-8 flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
    <a href="{{ route('admin.roles.index') }}"
       class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-400 transition-colors font-medium">
        Cancel
    </a>
    <button type="submit"
            class="bg-[#00BDE0] text-white px-6 py-2 rounded-lg hover:bg-[#00A5C7] transition-colors font-medium">
        {{ $submitLabel }}
    </button>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('select-all-permissions');
        const boxes = Array.from(document.querySelectorAll('.permission-checkbox'));

        function syncSelectAll() {
            selectAll.checked = boxes.length > 0 && boxes.every(box => box.checked);
        }

        selectAll.addEventListener('change', function () {
            boxes.forEach(box => { box.checked = selectAll.checked; });
        });

        boxes.forEach(box => box.addEventListener('change', syncSelectAll));
        syncSelectAll();
    });
</script>
@endpush
