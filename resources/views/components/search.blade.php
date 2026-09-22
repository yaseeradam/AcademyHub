@props(['placeholder' => 'Search...', 'showFilters' => true])

<div class="relative">
    <!-- Search Input -->
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8" />
                <path d="m21 21-4.35-4.35" />
            </svg>
        </div>
        <input type="text" class="input-search" placeholder="{{ $placeholder }}" x-data x-model="searchQuery"
            @input.debounce.300ms="performSearch">
    </div>

    @if ($showFilters)
        <!-- Filters Dropdown -->
        <div class="absolute top-full left-0 right-0 mt-2 p-4 bg-white rounded-lg shadow-lg border border-gray-100 hidden"
            x-data="{ open: false }" x-show="open" @click.away="open = false">
            <div class="space-y-4">
                <!-- Filter by Status -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2">Status</label>
                    <select class="select text-sm" x-model="filters.status">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="graduated">Graduated</option>
                    </select>
                </div>

                <!-- Filter by Class -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2">Class</label>
                    <select class="select text-sm" x-model="filters.class">
                        <option value="">All Classes</option>
                        <option value="jss1">JSS1</option>
                        <option value="jss2">JSS2</option>
                        <option value="jss3">JSS3</option>
                        <option value="sss1">SSS1</option>
                        <option value="sss2">SSS2</option>
                        <option value="sss3">SSS3</option>
                    </select>
                </div>

                <!-- Filter by Date Range -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2">Date Range</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="date" class="input-compact text-sm" x-model="filters.dateFrom" placeholder="From">
                        <input type="date" class="input-compact text-sm" x-model="filters.dateTo" placeholder="To">
                    </div>
                </div>

                <!-- Filter Actions -->
                <div class="flex gap-2 pt-2">
                    <button class="btn-primary text-xs" @click="applyFilters">Apply Filters</button>
                    <button class="btn-ghost text-xs" @click="clearFilters">Clear</button>
                </div>
            </div>
        </div>

        <!-- Filter Toggle Button -->
        <button class="absolute top-1/2 right-2 -translate-y-1/2 p-1 text-gray-400 hover:text-gray-600"
            @click="open = !open">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z" />
            </svg>
        </button>
    @endif
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('search', () => ({
            searchQuery: '',
            filters: {
                status: '',
                class: '',
                dateFrom: '',
                dateTo: ''
            },

            performSearch() {
                if (this.searchQuery.length > 2) {
                    // Show loading state
                    showNotification('Searching...', 'info');

                    // Simulate API call
                    setTimeout(() => {
                        this.updateResults();
                    }, 500);
                }
            },

            applyFilters() {
                showNotification('Applying filters...', 'info');
                setTimeout(() => {
                    this.updateResults();
                    showNotification('Filters applied', 'success');
                }, 300);
            },

            clearFilters() {
                this.filters = {
                    status: '',
                    class: '',
                    dateFrom: '',
                    dateTo: ''
                };
                this.updateResults();
                showNotification('Filters cleared', 'info');
            },

            updateResults() {
                // Emit custom event for parent components to handle
                window.dispatchEvent(new CustomEvent('search-updated', {
                    detail: {
                        query: this.searchQuery,
                        filters: this.filters
                    }
                }));
            }
        }));
    });
</script>