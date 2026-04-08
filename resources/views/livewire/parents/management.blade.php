<div class="space-y-6">
    <!-- Header -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 p-8 shadow-xl">
        <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxwYXRoIGQ9Ik0zNiAxOGMzLjMxNCAwIDYgMi42ODYgNiA2cy0yLjY4NiA2LTYgNi02LTIuNjg2LTYtNiAyLjY4Ni02IDYtNiIgc3Ryb2tlPSIjZmZmIiBzdHJva2Utd2lkdGg9IjIiIG9wYWNpdHk9Ii4xIi8+PC9nPjwvc3ZnPg==')] opacity-30"></div>
        <div class="relative">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white">Parent Management</h1>
                    <p class="mt-2 text-base text-indigo-50">Create parent accounts and link them to their children.</p>
                </div>
                <div class="flex items-center gap-3">
                    <button wire:click="openCreateModal" class="rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-indigo-600 shadow-lg transition-all hover:bg-indigo-50 hover:shadow-xl">
                        Create Parent Account
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Search -->
    <div class="rounded-2xl bg-white p-6 shadow-lg">
        <div class="flex items-center gap-4">
            <div class="flex-1">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search parents by name or email..." class="input w-full" />
            </div>
        </div>
    </div>

    <!-- Parents List -->
    <div class="rounded-2xl bg-white shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Parent</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Email</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold">Children</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Status</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($this->parents as $parent)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($parent->profile_photo_url)
                                        <img src="{{ $parent->profile_photo_url }}" class="h-10 w-10 rounded-full object-cover" />
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold">
                                            {{ substr($parent->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-semibold text-gray-900">{{ $parent->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $parent->custom_fields['phone'] ?? 'No phone' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-700">{{ $parent->email }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $parent->students_count }} {{ Str::plural('child', $parent->students_count) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $parent->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $parent->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="openLinkModal({{ $parent->id }})" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">
                                        Link Children
                                    </button>
                                    <button wire:click="deleteParent({{ $parent->id }})" wire:confirm="Are you sure you want to delete this parent account?" class="text-sm font-semibold text-red-600 hover:text-red-700">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <h3 class="mt-4 text-lg font-semibold text-gray-900">No parents found</h3>
                                <p class="mt-2 text-gray-500">Create the first parent account to get started.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($this->parents->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $this->parents->links() }}
            </div>
        @endif
    </div>

    <!-- Create Parent Modal -->
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-6 rounded-t-2xl">
                    <h3 class="text-xl font-bold text-white">Create Parent Account</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Full Name</label>
                        <input wire:model="name" type="text" class="input w-full" placeholder="Enter parent's full name" />
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                        <input wire:model="email" type="email" class="input w-full" placeholder="Enter email address" />
                        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                        <input wire:model="password" type="password" class="input w-full" placeholder="Enter password" />
                        @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Phone Number (Optional)</label>
                        <input wire:model="phone" type="text" class="input w-full" placeholder="Enter phone number" />
                        @error('phone') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="p-6 pt-0 flex gap-3">
                    <button wire:click="createParent" class="flex-1 btn-primary">
                        Create Account
                    </button>
                    <button wire:click="closeCreateModal" class="flex-1 btn-secondary">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Link Children Modal -->
    @if($showLinkModal && $this->selectedParent)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[80vh] overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-6 rounded-t-2xl">
                    <h3 class="text-xl font-bold text-white">Link Children to {{ $this->selectedParent->name }}</h3>
                </div>
                <div class="p-6 overflow-y-auto max-h-96">
                    <div class="space-y-3">
                        @foreach($this->availableStudents as $student)
                            <label class="flex items-center gap-3 p-3 rounded-lg border hover:bg-gray-50 cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    wire:model="selectedChildren" 
                                    value="{{ $student->id }}"
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                @if($student->passport_photo_url)
                                    <img src="{{ $student->passport_photo_url }}" class="h-10 w-10 rounded-full object-cover" />
                                @else
                                    <div class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-700 font-bold">
                                        {{ substr($student->first_name, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $student->full_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $student->admission_number }} • {{ $student->schoolClass?->name ?? 'Unassigned' }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="p-6 pt-0 flex gap-3">
                    <button wire:click="linkChildren" class="flex-1 btn-primary">
                        Link Selected Children
                    </button>
                    <button wire:click="closeLinkModal" class="flex-1 btn-secondary">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>