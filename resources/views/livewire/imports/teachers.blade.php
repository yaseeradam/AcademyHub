<div class="space-y-6">
    <x-page-header title="Smart AI Teacher Import"
        subtitle="Upload CSV or Excel files to bulk-load teachers. Our AI automatically maps your columns."
        accent="more">
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
                        Select Teacher Data File
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

                    {{-- Settings Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-slate-100">
                        <label class="flex flex-col p-4 rounded-2xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition cursor-pointer">
                            <span class="flex items-center gap-2 font-bold text-sm text-slate-700">
                                <input type="checkbox" wire:model.live="updateExisting" class="rounded text-indigo-600 focus:ring-indigo-500" />
                                Update Existing
                            </span>
                            <span class="text-xs text-slate-400 mt-1">Overwrite details if the email already exists in the system.</span>
                        </label>
                        <label class="flex flex-col p-4 rounded-2xl border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition cursor-pointer">
                            <span class="flex items-center gap-2 font-bold text-sm text-slate-700">
                                <input type="checkbox" wire:model.live="defaultActive" class="rounded text-indigo-600 focus:ring-indigo-500" />
                                Default Active
                            </span>
                            <span class="text-xs text-slate-400 mt-1">Set imported teachers as active by default unless specified.</span>
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
                        <div class="h-10 w-10 rounded-xl bg-white/10 flex items-center justify-center text-xl">
                            💡
                        </div>
                        <h4 class="text-base font-bold">Why use AI Layout Analysis?</h4>
                        <p class="text-xs text-white/80 leading-relaxed">
                            Traditional data importers force you to match header column names precisely. Our AI detects column mappings contextually.
                        </p>
                        <ul class="text-xs text-white/90 space-y-2">
                            <li class="flex items-start gap-2">
                                <span class="text-emerald-300 font-bold">✓</span>
                                <span><strong>Synonym Detection:</strong> AI detects that "Full Name", "teacher_name", or "Name" all map to the name field.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="text-emerald-300 font-bold">✓</span>
                                <span><strong>Auto Credentials:</strong> Automatically generates strong default passwords if left empty in the sheet.</span>
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

                        @if ($summary['errors'] === 0)
                            <button wire:click="import" class="w-full btn-indigo h-11 shadow-lg shadow-indigo-100">
                                Run Import Now
                            </button>
                        @else
                            <div class="text-xs text-rose-500 font-bold text-center">
                                Please resolve the spreadsheet errors shown below before running import.
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Errors Preview Table --}}
        @if ($errorsPreview !== [])
            <div class="bg-white/80 backdrop-blur-md border border-slate-100 rounded-3xl p-6 shadow-xl space-y-4">
                <h4 class="text-sm font-bold text-rose-600 flex items-center gap-2">
                    ⚠️ Validation Errors Detected (Dry Run)
                </h4>
                <div class="divide-y divide-slate-100 text-xs font-semibold text-slate-650 max-h-60 overflow-y-auto">
                    @foreach ($errorsPreview as $error)
                        <div class="py-2.5 flex items-start gap-2.5">
                            <span class="text-rose-500">•</span>
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    {{-- Step 2: Mapping Review --}}
    @if ($step === 2)
        <div class="bg-white/80 backdrop-blur-md border border-slate-100 rounded-3xl p-6 shadow-xl space-y-6 max-w-4xl mx-auto">
            <div>
                <h3 class="text-base font-bold text-slate-800">Confirm Column Mappings</h3>
                <p class="text-xs text-slate-500 mt-1">Review how your file columns match the system's standard teacher fields.</p>
            </div>

            <div class="space-y-4">
                @foreach (['name' => 'Full Name', 'email' => 'Email Address', 'password' => 'Password (Optional)', 'is_active' => 'Is Active (Optional)'] as $fieldKey => $fieldLabel)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-center p-4 rounded-2xl border border-slate-100 bg-slate-50/50">
                        <div>
                            <div class="text-sm font-bold text-slate-700">{{ $fieldLabel }}</div>
                            <div class="text-xs text-slate-400 mt-0.5">Map this field to a column in your file.</div>
                        </div>
                        <select wire:model="columnMapping.{{ $fieldKey }}" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-800 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            <option value="">-- Do Not Map --</option>
                            @foreach ($headers as $h)
                                <option value="{{ $h }}">{{ $h }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-between items-center pt-6 border-t border-slate-100">
                <button type="button" wire:click="$set('step', 1)" class="btn-outline">
                    Back to Upload
                </button>

                <button type="button" wire:click="import" class="btn-indigo h-11 px-8 shadow-lg shadow-indigo-100">
                    Import Teachers
                </button>
            </div>
        </div>
    @endif

    {{-- Step 3: Complete Report --}}
    @if ($step === 3)
        <div class="bg-white/80 backdrop-blur-md border border-slate-100 rounded-3xl p-6 shadow-xl space-y-6 max-w-2xl mx-auto text-center">
            <div class="h-16 w-16 bg-emerald-50 rounded-2xl flex items-center justify-center mx-auto text-emerald-500 text-3xl">
                ✓
            </div>
            
            <div>
                <h3 class="text-xl font-black text-slate-800">Import Process Complete!</h3>
                <p class="text-xs text-slate-500 mt-1">Here is a summary of the database modifications performed:</p>
            </div>

            <div class="grid grid-cols-3 gap-4 max-w-md mx-auto text-center text-xs">
                <div class="bg-emerald-50/50 rounded-2xl p-4 border border-emerald-100/30">
                    <div class="text-2xl font-black text-emerald-600">{{ $importReport['created'] ?? 0 }}</div>
                    <div class="text-slate-500 font-bold mt-1">Created</div>
                </div>
                <div class="bg-blue-50/50 rounded-2xl p-4 border border-blue-100/30">
                    <div class="text-2xl font-black text-blue-600">{{ $importReport['updated'] ?? 0 }}</div>
                    <div class="text-slate-500 font-bold mt-1">Updated</div>
                </div>
                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100/30">
                    <div class="text-2xl font-black text-slate-650">{{ $importReport['skipped'] ?? 0 }}</div>
                    <div class="text-slate-500 font-bold mt-1">Skipped</div>
                </div>
            </div>

            @if (!empty($importReport['errors']))
                <div class="text-left bg-rose-50 border border-rose-100/60 rounded-2xl p-5 mt-4 space-y-3">
                    <h4 class="text-xs font-bold text-rose-600">Import Errors ({{ count($importReport['errors']) }})</h4>
                    <div class="text-xs font-semibold text-slate-650 max-h-40 overflow-y-auto space-y-1.5">
                        @foreach ($importReport['errors'] as $err)
                            <div class="flex items-start gap-2">
                                <span class="text-rose-500">•</span>
                                <span>{{ $err }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="pt-6 border-t border-slate-100 flex justify-center gap-3">
                <a href="{{ route('imports.index') }}" class="btn-outline h-11">
                    All Imports
                </a>
                <button type="button" wire:click="$set('step', 1)" class="btn-indigo h-11">
                    Import Another File
                </button>
            </div>
        </div>
    @endif
</div>
