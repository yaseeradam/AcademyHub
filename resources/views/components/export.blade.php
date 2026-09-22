@props(['type' => 'students', 'filters' => []])

@php
    $exportEndpoint = match ($type) {
        'students' => route('students.export'),
        default => null,
    };
@endphp

<div x-data="exportManager({
    endpoint: @js($exportEndpoint),
    filters: @js($filters),
    type: @js($type)
})" class="relative">
    <!-- Export Button -->
    <button @click="showExportMenu = !showExportMenu" class="btn-outline text-xs flex items-center gap-2">
        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3" />
        </svg>
        Export
        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M6 9l6 6 6-6" />
        </svg>
    </button>

    <!-- Export Menu -->
    <div class="absolute top-full right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-100 z-10"
        x-show="showExportMenu" x-transition @click.away="showExportMenu = false">

        <div class="p-4">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Export Options</h3>

            <!-- Format Selection -->
            <div class="space-y-2 mb-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="format" value="csv" x-model="selectedFormat" class="checkbox-custom">
                    <span class="text-sm text-gray-700">CSV (Excel Compatible)</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="format" value="excel" x-model="selectedFormat" class="checkbox-custom">
                    <span class="text-sm text-gray-700">Excel (.xls)</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="format" value="pdf" x-model="selectedFormat" class="checkbox-custom">
                    <span class="text-sm text-gray-700">PDF Report</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="format" value="json" x-model="selectedFormat" class="checkbox-custom">
                    <span class="text-sm text-gray-700">JSON (Data)</span>
                </label>
            </div>

            <!-- Date Range -->
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-700 mb-2">Date Range</label>
                <div class="grid grid-cols-2 gap-2">
                    <input type="date" x-model="dateFrom" class="input-compact text-xs" placeholder="From">
                    <input type="date" x-model="dateTo" class="input-compact text-xs" placeholder="To">
                </div>
            </div>

            <!-- Include Options -->
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-700 mb-2">Include</label>
                <div class="space-y-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="includeHeaders" class="checkbox-custom">
                        <span class="text-xs text-gray-600">Headers</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="includeFilters" class="checkbox-custom">
                        <span class="text-xs text-gray-600">Current Filters</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="includeMetadata" class="checkbox-custom">
                        <span class="text-xs text-gray-600">Metadata</span>
                    </label>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2">
                <button @click="performExport" class="btn-primary text-xs flex-1">
                    Export Now
                </button>
                <button @click="scheduleExport" class="btn-outline text-xs flex-1">
                    Schedule
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function exportManager(config = {}) {
        return {
            endpoint: config.endpoint || null,
            filters: config.filters || {},
            type: config.type || 'students',
            showExportMenu: false,
            selectedFormat: 'csv',
            dateFrom: '',
            dateTo: '',
            includeHeaders: true,
            includeFilters: true,
            includeMetadata: false,

            performExport() {
                if (!this.endpoint) {
                    showNotification('Export is not configured for this page.', 'error');
                    return;
                }

                const allowedFormats = ['csv', 'json', 'excel', 'pdf'];
                if (!allowedFormats.includes(this.selectedFormat)) {
                    showNotification('Only CSV and JSON exports are currently supported.', 'warning');
                    return;
                }

                const params = new URLSearchParams();
                params.set('format', this.selectedFormat);
                params.set('include_headers', this.includeHeaders ? '1' : '0');

                if (this.dateFrom) {
                    params.set('from', this.dateFrom);
                }

                if (this.dateTo) {
                    params.set('to', this.dateTo);
                }

                if (this.includeFilters && this.filters) {
                    Object.entries(this.filters).forEach(([key, value]) => {
                        if (value !== null && value !== undefined && value !== '' && value !== 'all') {
                            params.set(key, value);
                        }
                    });
                }

                if (this.includeMetadata) {
                    params.set('include_metadata', '1');
                }

                showNotification(`Preparing ${this.type} export...`, 'info');
                window.location = `${this.endpoint}?${params.toString()}`;
                this.showExportMenu = false;
            },

            scheduleExport() {
                showNotification('Export scheduling is not available yet.', 'warning');
                this.showExportMenu = false;
            }
        }
    }
</script>