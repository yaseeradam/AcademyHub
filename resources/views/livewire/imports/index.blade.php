<div class="space-y-6">
    <x-page-header title="Imports" subtitle="Upload CSV files to bulk-load school data." accent="more" />

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <a href="{{ route('imports.students') }}" class="card p-6 transition hover:ring-brand-100">
            <div class="text-sm font-semibold text-gray-900">Import Students</div>
            <div class="mt-2 text-sm text-gray-600">Admission numbers, class/section, guardians.</div>
        </a>

        <a href="{{ route('imports.teachers') }}" class="card p-6 transition hover:ring-brand-100">
            <div class="text-sm font-semibold text-gray-900">Import Teachers</div>
            <div class="mt-2 text-sm text-gray-600">Creates teacher users from a CSV.</div>
        </a>
    </div>
</div>

