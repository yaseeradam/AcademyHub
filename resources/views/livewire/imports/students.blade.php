<div class="space-y-6">
    <x-page-header title="Smart AI Student Import"
        subtitle="Zero-configuration student importer. Our AI detects your file layout, automatically creates classes, sections, and provision custom fields dynamically."
        accent="students">
        <x-slot:actions>
            <a href="{{ route('imports.index') }}" class="btn-outline flex items-center gap-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                All Imports
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Progress Steps --}}
    <div class="relative flex justify-between items-center max-w-2xl mx-auto px-4 py-6">
        <div class="absolute left-4 right-4 top-1/2 h-0.5 bg-slate-100 -translate-y-1/2 z-0"></div>
        <div class="absolute left-4 top-1/2 h-0.5 bg-gradient-to-r from-violet-500 to-indigo-500 -translate-y-1/2 z-0 transition-all duration-500" 
             style="width: {{ $step == 1 ? '0%' : ($step == 2 ? '50%' : '100%') }}"></div>
        
        {{-- Step 1 --}}
        <div class="relative z-10 flex flex-col items-center">
            <div class="h-10 w-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 {{ $step >= 1 ? 'bg-gradient-to-br from-violet-500 to-indigo-500 text-white ring-4 ring-violet-100 shadow-md' : 'bg-slate-100 text-slate-400' }}">
                1
            </div>
            <span class="text-xs font-bold mt-2 {{ $step >= 1 ? 'text-indigo-600' : 'text-slate-400' }}">Upload File</span>
        </div>

        {{-- Step 2 --}}
        <div class="relative z-10 flex flex-col items-center">
            <div class="h-10 w-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 {{ $step >= 2 ? 'bg-gradient-to-br from-violet-500 to-indigo-500 text-white ring-4 ring-violet-100 shadow-md' : 'bg-slate-100 text-slate-400' }}">
                2
            </div>
            <span class="text-xs font-bold mt-2 {{ $step >= 2 ? 'text-indigo-600' : 'text-slate-400' }}">Review Mapping</span>
        </div>

        {{-- Step 3 --}}
        <div class="relative z-10 flex flex-col items-center">
            <div class="h-10 w-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 {{ $step >= 3 ? 'bg-gradient-to-br from-violet-500 to-indigo-500 text-white ring-4 ring-violet-100 shadow-md' : 'bg-slate-100 text-slate-400' }}">
                3
            </div>
            <span class="text-xs font-bold mt-2 {{ $step >= 3 ? 'text-indigo-600' : 'text-slate-400' }}">Complete</span>
        </div>
    </div>

    {{-- Step 1: Upload & Options --}}
    @if ($step === 1)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white/80 backdrop-blur-md border border-slate-100 rounded-3xl p-6 shadow-xl space-y-6">
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <svg class="h-5 w-5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        Select Student Data File
                    </h3>

                    <div 
                        x-data="{ dragging: false, uploading: false }"
                        x-on:livewire-upload-start="uploading = true"
                        x-on:livewire-upload-finish="uploading = false"
                        x-on:livewire-upload-error="uploading = false"
                        class="relative flex flex-col items-center justify-center border-2 border-dashed rounded-2xl p-10 text-center transition-all cursor-pointer"
                        :class="dragging ? 'border-violet-500 bg-violet-50/50' : 'border-slate-200 hover:border-violet-400 hover:bg-slate-50/30'"
                        @dragover.prevent="dragging = true"
                        @dragleave.prevent="dragging = false"
                        @drop.prevent="dragging = false"
                    >
                        <input 
                            wire:model="file" 
                            type="file" 
                            accept=".csv,.txt,.xlsx,.xls,.ods" 
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20" 
                        />
                        
                        @if ($file)
                            <div class="space-y-2 z-10">
                                <div class="h-14 w-14 rounded-2xl bg-indigo-50 flex items-center justify-center mx-auto">
                                    <svg class="h-8 w-8 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="text-sm font-bold text-slate-700">{{ $file->getClientOriginalName() }}</div>
                                <div class="text-xs text-slate-400">{{ number_format($file->getSize() / 1024, 1) }} KB</div>
                            </div>
                        @else
                            <div class="space-y-2 z-10">
                                <div class="h-14 w-14 rounded-2xl bg-slate-50 flex items-center justify-center mx-auto">
                                    <svg class="h-8 w-8 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 13h6m-3-3v6m-9 1V4a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                                    </svg>
                                </div>
                                <div class="text-sm font-bold text-slate-600">Drag & drop your file here, or <span class="text-indigo-600 hover:text-indigo-700 underline">browse</span></div>
                                <div class="text-xs text-slate-400">Supports Excel (.xlsx, .xls, .ods) and CSV up to 10MB</div>
                            </div>
                        @endif

                        {{-- Uploading Loader --}}
                        <div x-show="uploading" class="absolute inset-0 bg-white/95 rounded-2xl z-30 flex flex-col items-center justify-center space-y-3">
                            <svg class="h-8 w-8 text-indigo-500 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                            <span class="text-sm font-bold text-slate-600">Uploading file to server...</span>
                        </div>
                    </div>

                    @error('file')
                        <div class="rounded-xl bg-rose-50 border border-rose-100 p-4 text-xs font-semibold text-rose-600 flex items-center gap-2">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            {{ $message }}
                        </div>
                    @enderror

                    {{-- Dynamic Settings Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-4 border-t border-slate-100">
                        <label class="flex flex-col p-4 rounded-2xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition cursor-pointer">
                            <span class="flex items-center gap-2 font-bold text-sm text-slate-700">
                                <input type="checkbox" wire:model.live="updateExisting" class="rounded text-indigo-600 focus:ring-indigo-500" />
                                Update Existing
                            </span>
                            <span class="text-xs text-slate-400 mt-1">Overwrite student details if the admission number exists.</span>
                        </label>
                        <label class="flex flex-col p-4 rounded-2xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition cursor-pointer">
                            <span class="flex items-center gap-2 font-bold text-sm text-slate-700">
                                <input type="checkbox" wire:model.live="createMissingClasses" class="rounded text-indigo-600 focus:ring-indigo-500" />
                                Create Classes
                            </span>
                            <span class="text-xs text-slate-400 mt-1">Automatically create classes parsed from the file.</span>
                        </label>
                        <label class="flex flex-col p-4 rounded-2xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition cursor-pointer">
                            <span class="flex items-center gap-2 font-bold text-sm text-slate-700">
                                <input type="checkbox" wire:model.live="createMissingSections" class="rounded text-indigo-600 focus:ring-indigo-500" />
                                Create Sections
                            </span>
                            <span class="text-xs text-slate-400 mt-1">Automatically create sections / arms under their classes.</span>
                        </label>
                    </div>

                    {{-- Actions --}}
                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <button 
                            type="button" 
                            wire:click="analyze" 
                            class="btn-outline h-11"
                            {{ empty($file) ? 'disabled' : '' }}
                        >
                            Standard Analyze
                        </button>
                        
                        <button
                            type="button"
                            wire:click="analyzeWithAI"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 px-6 h-11 text-sm font-bold text-white shadow-lg hover:from-violet-700 hover:to-indigo-700 transition disabled:opacity-50"
                            {{ empty($file) ? 'disabled' : '' }}
                            wire:loading.attr="disabled"
                            wire:target="analyzeWithAI"
                        >
                            <span wire:loading wire:target="analyzeWithAI" class="h-4 w-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                            <img wire:loading.remove wire:target="analyzeWithAI" src="{{ asset('ai.png') }}" class="h-4 w-4 object-contain brightness-0 invert" alt="AI" />
                            <span wire:loading.remove wire:target="analyzeWithAI">Analyze Layout with AI</span>
                            <span wire:loading wire:target="analyzeWithAI">AI Analyzing Layout…</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Sidebar Smart Info --}}
            <div class="space-y-6">
                <div class="bg-gradient-to-br from-violet-600 to-indigo-600 text-white rounded-3xl p-6 shadow-xl relative overflow-hidden">
                    <div class="absolute -right-10 -bottom-10 h-40 w-40 bg-white/5 rounded-full blur-2xl"></div>
                    <div class="relative space-y-4">
                        <div class="h-10 w-10 rounded-xl bg-white/10 flex items-center justify-center">
                            <img src="{{ asset('ai.png') }}" class="h-6 w-6 object-contain brightness-0 invert" alt="AI" />
                        </div>
                        <h4 class="text-base font-bold">Why use AI Layout Analysis?</h4>
                        <p class="text-xs text-white/80 leading-relaxed">
                            Traditional data importers force you to match header column names precisely, requiring you to manually edit your excel files first.
                        </p>
                        <ul class="text-xs text-white/90 space-y-2">
                            <li class="flex items-start gap-2">
                                <span class="text-emerald-300 font-bold">✓</span>
                                <span><strong>Synonym Detection:</strong> AI detects that "Sex", "gender", or "student_gender" all map to the same field.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-emerald-300 font-bold">✓</span>
                                <span><strong>Custom Fields:</strong> Auto-provisions custom fields like "Blood Pressure" or "Religion" to the database.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-emerald-300 font-bold">✓</span>
                                <span><strong>Fast & Safe:</strong> Only reads 5 rows to detect mappings, avoiding token limits and keeping your import quick.</span>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Dry Run Summary --}}
                @if ($summary)
                    <div class="bg-white/80 backdrop-blur-md border border-slate-100 rounded-3xl p-6 shadow-xl space-y-4">
                        <h4 class="text-sm font-bold text-slate-800">Dry-Run Analysis Summary</h4>
                        <div class="grid grid-cols-2 gap-2 text-center text-xs">
                            <div class="bg-emerald-50 rounded-xl p-3 border border-emerald-100/50">
                                <div class="text-lg font-black text-emerald-600">{{ $summary['to_create'] }}</div>
                                <div class="text-slate-500 font-semibold mt-0.5">To Create</div>
                            </div>
                            <div class="bg-blue-50 rounded-xl p-3 border border-blue-100/50">
                                <div class="text-lg font-black text-blue-600">{{ $summary['to_update'] }}</div>
                                <div class="text-slate-500 font-semibold mt-0.5">To Update</div>
                            </div>
                            <div class="bg-slate-50 rounded-xl p-3 border border-slate-100/50">
                                <div class="text-lg font-black text-slate-600">{{ $summary['to_skip_existing'] }}</div>
                                <div class="text-slate-500 font-semibold mt-0.5">Skip Existing</div>
                            </div>
                            <div class="bg-rose-50 rounded-xl p-3 border border-rose-100/50">
                                <div class="text-lg font-black text-rose-600">{{ $summary['errors'] }}</div>
                                <div class="text-slate-500 font-semibold mt-0.5">Errors</div>
                            </div>
                        </div>

                        @if ($errorsPreview)
                            <div class="rounded-xl border border-rose-100 bg-rose-50/50 p-4 space-y-2">
                                <div class="text-xs font-bold text-rose-900 flex items-center gap-1">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    Issues Discovered
                                </div>
                                <ul class="list-disc pl-4 text-xs text-rose-800 space-y-1">
                                    @foreach ($errorsPreview as $err)
                                        <li>{{ $err }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <button 
                            type="button" 
                            wire:click="import" 
                            class="w-full btn-primary h-10 mt-2"
                            {{ $summary['errors'] > 0 ? 'disabled' : '' }}
                        >
                            Confirm Manual Import
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Step 2: Mapping Review & Dynamic Fields Configuration --}}
    @if ($step === 2)
        <div class="bg-white/80 backdrop-blur-md border border-slate-100 rounded-3xl p-6 shadow-xl space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('ai.png') }}" class="h-6 w-6 object-contain" alt="AI" />
                    <div>
                        <h3 class="text-base font-bold text-slate-800">Verify Detected Column Mappings</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Review standard fields alignment and toggle auto-creation of custom fields.</p>
                    </div>
                </div>
                <button wire:click="resetWizard" class="btn-outline h-9 px-3 text-xs">✕ Start Over</button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                {{-- Column Mappings Selector --}}
                <div class="lg:col-span-2 space-y-6">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">Standard Student Fields — Map each file column</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($columnMapping as $fieldKey => $mappedHeader)
                            <div class="flex flex-col p-4 border border-slate-100 bg-slate-50/30 rounded-2xl">
                                <label class="text-xs font-bold text-slate-700 capitalize flex items-center justify-between">
                                    <span>{{ str_replace('_', ' ', $fieldKey) }}</span>
                                    @if(in_array($fieldKey, ['first_name', 'last_name', 'class_name', 'section_name']))
                                        <span class="text-[9px] text-rose-500 font-bold uppercase bg-rose-50 px-1.5 py-0.5 rounded">Required</span>
                                    @endif
                                </label>
                                <select 
                                    wire:model.live="columnMapping.{{ $fieldKey }}"
                                    class="mt-2 text-xs rounded-xl border border-slate-200 bg-white px-3 py-2 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                                >
                                    <option value="">-- Skip Field --</option>
                                    @foreach ($headers as $header)
                                        <option value="{{ $header }}">{{ $header }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Extra columns auto-saved as custom fields --}}
                <div class="space-y-4">
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">Extra Columns → Custom Fields</h4>
                        <p class="text-xs text-slate-400 mt-1">All columns not mapped to a standard field are automatically saved as student custom fields.</p>
                    </div>

                    @if (empty($detectedCustomFields))
                        <div class="rounded-2xl border border-slate-100 bg-slate-50/50 p-4 text-center text-xs text-slate-400">
                            All columns are mapped to standard fields.
                        </div>
                    @else
                        <div class="space-y-2">
                            @foreach ($detectedCustomFields as $field)
                                <div class="flex items-center gap-3 p-3 border border-indigo-100 bg-indigo-50/40 rounded-xl">
                                    <div class="h-7 w-7 rounded-lg bg-indigo-100 flex items-center justify-center flex-shrink-0">
                                        <svg class="h-3.5 w-3.5 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold text-slate-800 truncate">{{ $field['label'] }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $field['type'] }}</div>
                                    </div>
                                    <span class="ml-auto flex-shrink-0 inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[9px] font-bold text-emerald-700">
                                        <span class="h-1 w-1 rounded-full bg-emerald-500"></span> Auto
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="bg-indigo-50/50 border border-indigo-100/50 rounded-2xl p-4 space-y-2">
                        <h4 class="text-xs font-bold text-indigo-900">Import Settings</h4>
                        <div class="space-y-1.5 text-xs text-indigo-800">
                            <div class="flex justify-between">
                                <span>Auto-create classes:</span>
                                <span class="font-bold {{ $createMissingClasses ? 'text-emerald-600' : 'text-slate-400' }}">{{ $createMissingClasses ? 'Yes' : 'No' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Auto-create sections:</span>
                                <span class="font-bold {{ $createMissingSections ? 'text-emerald-600' : 'text-slate-400' }}">{{ $createMissingSections ? 'Yes' : 'No' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Update existing:</span>
                                <span class="font-bold {{ $updateExisting ? 'text-emerald-600' : 'text-slate-400' }}">{{ $updateExisting ? 'Yes' : 'No' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 5 Row Sample Mapped Data Preview Table --}}
            <div class="border-t border-slate-100 pt-6 space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">First 5 Rows Preview — All {{ count($headers) }} Columns</h4>
                <div class="overflow-x-auto border border-slate-100 rounded-2xl bg-slate-50/10">
                    <table class="min-w-full text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100 text-slate-500">
                                @foreach ($headers as $header)
                                    @php
                                        $mappedTo = array_search($header, $columnMapping);
                                        $isCustom = !$mappedTo && collect($detectedCustomFields)->pluck('csv_header')->contains($header);
                                    @endphp
                                    <th class="px-4 py-3 text-left font-bold whitespace-nowrap {{ $isCustom ? 'text-indigo-600 bg-indigo-50/30' : 'text-slate-600' }}">
                                        {{ $header }}
                                        @if($mappedTo)
                                            <div class="text-[9px] font-normal text-slate-400">→ {{ str_replace('_', ' ', $mappedTo) }}</div>
                                        @elseif($isCustom)
                                            <div class="text-[9px] font-normal text-indigo-400">custom field</div>
                                        @endif
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($sampleRows as $row)
                                <tr class="hover:bg-slate-50/50">
                                    @foreach ($headers as $i => $header)
                                        @php
                                            $isCustom = !array_search($header, $columnMapping) && collect($detectedCustomFields)->pluck('csv_header')->contains($header);
                                        @endphp
                                        <td class="px-4 py-3 whitespace-nowrap {{ $isCustom ? 'text-indigo-700 bg-indigo-50/10' : 'text-slate-600' }}">{{ $row[$i] ?? '' }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Process Confirmation Actions --}}
            <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                <button type="button" wire:click="resetWizard" class="btn-outline h-11">Cancel</button>
                <button 
                    type="button" 
                    wire:click="importMappedData" 
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 px-6 h-11 text-sm font-bold text-white shadow-lg transition"
                    wire:loading.attr="disabled"
                    wire:target="importMappedData"
                >
                    <span wire:loading wire:target="importMappedData" class="h-4 w-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                    <svg wire:loading.remove wire:target="importMappedData" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Confirm & Start Processing Import
                </button>
            </div>
        </div>
    @endif

    {{-- Step 3: Success Summary Card --}}
    @if ($step === 3)
        <div class="max-w-2xl mx-auto bg-white border border-slate-100 rounded-3xl shadow-2xl p-8 space-y-6 text-center">
            
            {{-- Big Checked Icon --}}
            <div class="h-20 w-20 rounded-full bg-emerald-50 border border-emerald-100 flex items-center justify-center mx-auto text-emerald-500 shadow-inner">
                <svg class="h-10 w-10 animate-bounce" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            <div class="space-y-2">
                <h3 class="text-xl font-bold text-slate-800">Student Data Imported Successfully!</h3>
                <p class="text-xs text-slate-500">The entire file was processed safely in the database using the AI mapping profile.</p>
            </div>

            {{-- Summary Details Grid --}}
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-center">
                <div class="bg-slate-50/50 rounded-2xl p-4 border border-slate-100/50">
                    <div class="text-2xl font-black text-indigo-600">{{ $importReport['created'] }}</div>
                    <div class="text-xs text-slate-500 font-semibold mt-1">Students Created</div>
                </div>
                <div class="bg-slate-50/50 rounded-2xl p-4 border border-slate-100/50">
                    <div class="text-2xl font-black text-indigo-600">{{ $importReport['updated'] }}</div>
                    <div class="text-xs text-slate-500 font-semibold mt-1">Students Updated</div>
                </div>
                <div class="bg-slate-50/50 rounded-2xl p-4 border border-slate-100/50">
                    <div class="text-2xl font-black text-indigo-600">{{ $importReport['skipped'] }}</div>
                    <div class="text-xs text-slate-500 font-semibold mt-1">Students Skipped</div>
                </div>
                <div class="bg-slate-50/50 rounded-2xl p-4 border border-slate-100/50">
                    <div class="text-2xl font-black text-indigo-600">{{ $importReport['classes_created'] }}</div>
                    <div class="text-xs text-slate-500 font-semibold mt-1">Classes Created</div>
                </div>
                <div class="bg-slate-50/50 rounded-2xl p-4 border border-slate-100/50">
                    <div class="text-2xl font-black text-indigo-600">{{ $importReport['sections_created'] }}</div>
                    <div class="text-xs text-slate-500 font-semibold mt-1">Sections Created</div>
                </div>
                <div class="bg-slate-50/50 rounded-2xl p-4 border border-slate-100/50">
                    <div class="text-2xl font-black text-indigo-600">{{ $importReport['custom_fields_created'] }}</div>
                    <div class="text-xs text-slate-500 font-semibold mt-1">Custom Fields Added</div>
                </div>
            </div>

            {{-- Error Logs if Any --}}
            @if (!empty($importReport['errors']))
                <div class="text-left rounded-2xl border border-rose-100 bg-rose-50/50 p-5 space-y-3">
                    <h4 class="text-xs font-bold text-rose-900 flex items-center gap-1.5">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Row Processing Failures ({{ count($importReport['errors']) }})
                    </h4>
                    <div class="max-h-40 overflow-y-auto space-y-1">
                        @foreach ($importReport['errors'] as $err)
                            <div class="text-xs text-rose-800 leading-relaxed">• {{ $err }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex justify-center gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('students.index') }}" class="btn-outline h-11 flex items-center justify-center px-6">Go to Students Directory</a>
                <button type="button" wire:click="resetWizard" class="btn-primary h-11 px-6">Import Another File</button>
            </div>
        </div>
    @endif
</div>
