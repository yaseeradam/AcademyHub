@props(['items' => [], 'actions' => ['delete', 'export', 'edit']])

<div x-data="bulkOperations()" class="space-y-4">
    <!-- Bulk Actions Bar -->
    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 hidden" x-show="selectedItems.length > 0" x-transition>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="text-sm text-gray-700">
                <span x-text="selectedItems.length"></span> items selected
            </div>

            <div class="flex flex-wrap gap-2">
                <!-- Select All Checkbox -->
                <div class="flex items-center gap-2">
                    <input type="checkbox" class="checkbox-custom" @change="toggleSelectAll"
                        :checked="selectedItems.length === items.length">
                    <span class="text-xs text-gray-600">Select All</span>
                </div>

                <!-- Bulk Actions -->
                @if (in_array('delete', $actions))
                    <button class="btn-warning text-xs" @click="confirmBulkDelete">
                        <svg class="h-3 w-3 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path
                                d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6h14zM10 11v6M14 11v6" />
                        </svg>
                        Delete
                    </button>
                @endif

                @if (in_array('export', $actions))
                    <button class="btn-outline text-xs" @click="bulkExport">
                        <svg class="h-3 w-3 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3" />
                        </svg>
                        Export
                    </button>
                @endif

                @if (in_array('edit', $actions))
                    <button class="btn-primary text-xs" @click="bulkEdit">
                        <svg class="h-3 w-3 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path
                                d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                        Edit
                    </button>
                @endif

                <!-- Clear Selection -->
                <button class="btn-ghost text-xs" @click="clearSelection">
                    Clear
                </button>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="fixed inset-0 z-50 hidden" x-show="showModal" x-transition>
        <div class="fixed inset-0 bg-black/50" @click="showModal = false"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6 animate-slide-up">
                <h3 class="text-lg font-semibold text-gray-900 mb-2" x-text="modalTitle"></h3>
                <p class="text-sm text-gray-600 mb-4" x-text="modalMessage"></p>

                <div class="flex gap-3 justify-end">
                    <button class="btn-ghost text-sm" @click="showModal = false">Cancel</button>
                    <button class="btn-warning text-sm" @click="confirmAction">Confirm</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function bulkOperations() {
        return {
            selectedItems: [],
            items: [],
            showModal: false,
            modalTitle: '',
            modalMessage: '',
            pendingAction: null,

            init() {
                this.updateItems();
                // Listen for checkbox changes
                this.$watch('$el', () => {
                    this.updateSelectedItems();
                });

                // Listen for search updates
                window.addEventListener('search-updated', (e) => {
                    this.clearSelection();
                });
            },

            updateItems() {
                const checkboxes = document.querySelectorAll('.checkbox-custom');
                this.items = Array.from(checkboxes);
            },

            updateSelectedItems() {
                const checkboxes = document.querySelectorAll('.checkbox-custom:checked');
                this.selectedItems = Array.from(checkboxes).map(cb => cb.value);
            },

            toggleSelectAll(event) {
                const checkboxes = document.querySelectorAll('.checkbox-custom');
                checkboxes.forEach(cb => cb.checked = event.target.checked);
                this.updateSelectedItems();
            },

            clearSelection() {
                const checkboxes = document.querySelectorAll('.checkbox-custom');
                checkboxes.forEach(cb => cb.checked = false);
                this.selectedItems = [];
            },

            confirmBulkDelete() {
                this.modalTitle = 'Delete Selected Items';
                this.modalMessage = `Are you sure you want to delete ${this.selectedItems.length} selected items? This action cannot be undone.`;
                this.pendingAction = 'delete';
                this.showModal = true;
            },

            bulkExport() {
                const format = prompt('Export format (csv, excel, pdf):', 'csv');
                if (format) {
                    showNotification(`Exporting ${this.selectedItems.length} items as ${format.toUpperCase()}...`, 'info');
                    setTimeout(() => {
                        showNotification('Export completed successfully!', 'success');
                    }, 1500);
                }
            },

            bulkEdit() {
                showNotification(`Opening bulk edit for ${this.selectedItems.length} items...`, 'info');
                // Implement bulk edit logic
            },

            confirmAction() {
                if (this.pendingAction === 'delete') {
                    showNotification(`Deleting ${this.selectedItems.length} items...`, 'warning');
                    setTimeout(() => {
                        showNotification('Items deleted successfully', 'success');
                        this.clearSelection();
                        this.showModal = false;
                    }, 1500);
                }
            }
        }
    }
</script>