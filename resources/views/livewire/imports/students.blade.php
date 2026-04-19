<div class="space-y-6">
    <x-page-header title="Import Students"
        subtitle="Supports CSV and Excel (.xlsx, .xls, .ods). Required columns: admission_number, first_name, last_name, gender, class_name, section_name."
        accent="students">
        <x-slot:actions>
            <a href="{{ route('imports.index') }}" class="btn-outline">All Imports</a>
        </x-slot:actions>
    </x-page-header>

    <div
        x-data="{ uploading: false }"
        x-on:livewire-upload-start="uploading = true"
        x-on:livewire-upload-finish="uploading = false"
        x-on:livewire-upload-error="uploading = false"
        class="card-padded space-y-4"
    >
        {{-- File Upload --}}
        <div>
            <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">File (CSV or Excel)</label>
            <input wire:model="file" type="file" accept=".csv,.txt,.xlsx,.xls,.ods" class="mt-2" />
            <span x-show="uploading" class="mt-1 flex items-center gap-1 text-xs text-slate-500">
                <svg class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                Uploading…
            </span>
            @error('file')
                <div class="mt-2 text-sm font-semibold text-orange-700">{{ $message }}</div>
            @enderror
        </div>

        {{-- Options --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" wire:model.live="updateExisting" />
                Update existing students
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" wire:model.live="createMissingClasses" />
                Create missing classes
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" wire:model.live="createMissingSections" />
                Create missing sections
            </label>
        </div>

        {{-- Actions --}}
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" wire:click="analyze" class="btn-outline" :disabled="{{ empty($file) ? 'true' : 'false' }} || uploading" wire:loading.attr="disabled">
                Analyze
            </button>

            <button
                type="button"
                wire:click="analyzeWithAI"
                :disabled="{{ empty($file) ? 'true' : 'false' }} || uploading"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-700 disabled:opacity-50"
            >
                <svg wire:loading wire:target="analyzeWithAI" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                </svg>
                <svg wire:loading.remove wire:target="analyzeWithAI" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
                <span wire:loading.remove wire:target="analyzeWithAI">Analyze with AI</span>
                <span wire:loading wire:target="analyzeWithAI">Analyzing…</span>
            </button>

            <button type="button" wire:click="import" class="btn-primary" :disabled="{{ empty($file) ? 'true' : 'false' }} || uploading" wire:loading.attr="disabled">
                Import
            </button>
        </div>

        {{-- Summary --}}
        @if ($summary)
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-5">
                <x-stat-card label="Valid Rows"     :value="number_format((int) ($summary['rows_valid']       ?? 0))" />
                <x-stat-card label="Create"         :value="number_format((int) ($summary['to_create']        ?? 0))" />
                <x-stat-card label="Update"         :value="number_format((int) ($summary['to_update']        ?? 0))" />
                <x-stat-card label="Skip Existing"  :value="number_format((int) ($summary['to_skip_existing'] ?? 0))" />
                <x-stat-card label="Removed/Errors" :value="number_format((int) ($summary['errors']           ?? 0))" />
            </div>
        @endif

        {{-- Standard errors --}}
        @if ($errorsPreview)
            <div class="rounded-xl border border-orange-200 bg-orange-50/60 p-4">
                <div class="text-sm font-semibold text-orange-900">Issues found</div>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-orange-900">
                    @foreach ($errorsPreview as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- ── AI Confirmation Modal ─────────────────────────────────────────── --}}
    @if ($showConfirmation)
        <div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-black/50 px-4 py-10">
            <div class="w-full max-w-3xl rounded-2xl bg-white shadow-2xl">
                <div class="flex items-center justify-between rounded-t-2xl bg-violet-600 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        <h2 class="text-lg font-bold text-white">AI Analysis — Review Before Importing</h2>
                    </div>
                    <button wire:click="cancelAiImport" class="text-white/70 hover:text-white">✕</button>
                </div>

                <div class="space-y-5 p-6">
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        <div class="rounded-xl bg-green-50 p-3 text-center">
                            <div class="text-2xl font-black text-green-700">{{ $summary['to_create'] ?? 0 }}</div>
                            <div class="text-xs text-green-600">To Create</div>
                        </div>
                        <div class="rounded-xl bg-blue-50 p-3 text-center">
                            <div class="text-2xl font-black text-blue-700">{{ $summary['to_update'] ?? 0 }}</div>
                            <div class="text-xs text-blue-600">To Update</div>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3 text-center">
                            <div class="text-2xl font-black text-slate-700">{{ $summary['to_skip_existing'] ?? 0 }}</div>
                            <div class="text-xs text-slate-600">Skip Existing</div>
                        </div>
                        <div class="rounded-xl bg-red-50 p-3 text-center">
                            <div class="text-2xl font-black text-red-700">{{ count($aiRemoved) }}</div>
                            <div class="text-xs text-red-600">Removed by AI</div>
                        </div>
                    </div>

                    @if ($aiChanges)
                        <div class="rounded-xl border border-violet-200 bg-violet-50 p-4">
                            <div class="mb-2 text-sm font-bold text-violet-900">Changes made by AI</div>
                            <ul class="list-disc space-y-1 pl-5 text-sm text-violet-800">
                                @foreach ($aiChanges as $change)
                                    <li>{{ $change }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($aiRemoved)
                        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                            <div class="mb-2 text-sm font-bold text-red-900">Rows removed by AI (will NOT be imported)</div>
                            <ul class="list-disc space-y-1 pl-5 text-sm text-red-800">
                                @foreach ($aiRemoved as $r)
                                    <li>
                                        <span class="font-semibold">Row {{ $r['row'] ?? '?' }}:</span>
                                        {{ $r['reason'] ?? 'Invalid data' }}
                                        @if (! empty($r['data']))
                                            <span class="ml-1 text-xs text-red-500">({{ \Illuminate\Support\Str::limit($r['data'], 60) }})</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($aiRows)
                        <div>
                            <div class="mb-2 text-sm font-bold text-slate-700">Preview (first 10 rows)</div>
                            <div class="overflow-x-auto rounded-xl border border-slate-200">
                                <table class="min-w-full text-xs">
                                    <thead class="bg-slate-100">
                                        <tr>
                                            @foreach (['Adm #', 'First Name', 'Last Name', 'Gender', 'Class', 'Section', 'Status'] as $h)
                                                <th class="px-3 py-2 text-left font-semibold text-slate-600">{{ $h }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach (array_slice($aiRows, 0, 10) as $row)
                                            <tr class="hover:bg-slate-50">
                                                <td class="px-3 py-2 font-mono">{{ $row['admission_number'] ?? '' }}</td>
                                                <td class="px-3 py-2">{{ $row['first_name'] ?? '' }}</td>
                                                <td class="px-3 py-2">{{ $row['last_name'] ?? '' }}</td>
                                                <td class="px-3 py-2">{{ $row['gender'] ?? '' }}</td>
                                                <td class="px-3 py-2">{{ $row['class_name'] ?? '' }}</td>
                                                <td class="px-3 py-2">{{ $row['section_name'] ?? '' }}</td>
                                                <td class="px-3 py-2">{{ $row['status'] ?: 'Active' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @if (count($aiRows) > 10)
                                    <div class="px-3 py-2 text-xs text-slate-500">… and {{ count($aiRows) - 10 }} more rows</div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
                        <button wire:click="cancelAiImport" class="btn-outline">Cancel</button>
                        <button wire:click="confirmAiImport" class="inline-flex items-center gap-2 rounded-lg bg-violet-600 px-5 py-2 text-sm font-bold text-white hover:bg-violet-700">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M5 13l4 4L19 7"/>
                            </svg>
                            Confirm & Import {{ count($aiRows) }} Students
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
