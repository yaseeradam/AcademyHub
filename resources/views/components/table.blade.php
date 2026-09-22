@props(['sortable' => false, 'selectable' => false, 'items' => []])

<div x-data="enhancedTable({{ $sortable ? 'true' : 'false' }}, {{ $selectable ? 'true' : 'false' }})"
    class="overflow-hidden rounded-2xl bg-white shadow-soft ring-1 ring-gray-100">
    <!-- Table Header with Search -->
    @if ($sortable || $selectable)
        <div class="border-b border-gray-100 p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                @if ($selectable)
                    <div class="flex items-center gap-3">
                        <input type="checkbox" class="checkbox-custom" @change="toggleSelectAll"
                            :checked="selectedItems.length === items.length">
                        <span class="text-sm text-gray-600">
                            <span x-text="selectedItems.length"></span> / <span x-text="items.length"></span> selected
                        </span>
                    </div>
                @endif

                @if ($sortable)
                    <div class="flex items-center gap-2">
                        <button class="btn-ghost text-xs" @click="sortBy('name')">
                            Sort by Name
                            <svg class="h-3 w-3 inline ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M7 10l5 5 5-5M7 14l5 5 5-5" />
                            </svg>
                        </button>
                        <button class="btn-ghost text-xs" @click="sortBy('date')">
                            Sort by Date
                            <svg class="h-3 w-3 inline ml-1" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M7 10l5 5 5-5M7 14l5 5 5-5" />
                            </svg>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table {{ $attributes->merge(['class' => 'w-full text-left border-collapse']) }}>
            {{ $slot }}
        </table>
    </div>

    <!-- Loading Overlay -->
    <div class="absolute inset-0 bg-white/80 flex items-center justify-center hidden" x-show="loading" x-transition>
        <div class="flex items-center gap-3">
            <div class="animate-spin h-5 w-5 border-2 border-brand-500 border-t-transparent rounded-full"></div>
            <span class="text-sm text-gray-600">Loading...</span>
        </div>
    </div>
</div>

<script>
    function enhancedTable(sortable = false, selectable = false) {
        return {
            loading: false,
            selectedItems: [],
            items: [],
            sortField: null,
            sortDirection: 'asc',

            init() {
                if (selectable) {
                    this.updateItems();
                    this.updateSelectedItems();
                }
            },

            updateItems() {
                const checkboxes = this.$el.querySelectorAll('tbody .checkbox-custom');
                this.items = Array.from(checkboxes);
            },

            toggleSelectAll(event) {
                const checkboxes = this.$el.querySelectorAll('tbody .checkbox-custom');
                checkboxes.forEach(cb => cb.checked = event.target.checked);
                this.updateSelectedItems();
            },

            updateSelectedItems() {
                const checkboxes = this.$el.querySelectorAll('tbody .checkbox-custom:checked');
                this.selectedItems = Array.from(checkboxes).map(cb => cb.value);
            },

            sortBy(field) {
                if (!sortable) return;

                this.loading = true;
                this.sortField = field;
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';

                // Simulate sorting
                setTimeout(() => {
                    this.loading = false;
                    showNotification(`Sorted by ${field} (${this.sortDirection})`, 'info');
                }, 500);
            }
        }
    }
</script>