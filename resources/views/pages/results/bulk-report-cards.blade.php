@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\SchoolClass> $classes */
    $user = auth()->user();
@endphp

@extends('layouts.app')

@section('content')
    <div class="space-y-8" x-data="{ 
        classId: '', 
        session: '{{ config('academyhub.current_session', '2025/2026') }}',
        term: '{{ config('academyhub.current_term', 1) }}',
        template: '',
        students: [], 
        selectedStudents: [], 
        loading: false,
        generating: false,
        
        async fetchStudents() {
            if (!this.classId) {
                this.students = [];
                this.selectedStudents = [];
                return;
            }
            this.loading = true;
            try {
                const response = await fetch(`/api/classes/${this.classId}/students`);
                this.students = await response.json();
                // Select all by default
                this.selectedStudents = this.students.map(s => s.id);
            } catch (error) {
                console.error('Failed to fetch students:', error);
            } finally {
                this.loading = false;
            }
        },

        toggleAll() {
            if (this.selectedStudents.length === this.students.length) {
                this.selectedStudents = [];
            } else {
                this.selectedStudents = this.students.map(s => s.id);
            }
        },

        preview(studentId) {
            const params = new URLSearchParams({
                student_id: studentId,
                session: this.session,
                term: this.term,
                template: this.template
            });
            
            // Collect display options
            const options = ['show_psychomotor', 'show_attendance', 'show_position', 'show_class_average'];
            options.forEach(opt => {
                const el = document.querySelector(`input[name='options[${opt}]']`);
                if (el && el.checked) {
                    params.append(`options[${opt}]`, '1');
                }
            });

            window.open(`{{ route('results.bulk-report-cards.preview') }}?${params.toString()}`, '_blank');
        }
    }">
        <x-page-header title="Bulk Report Cards" subtitle="Generate high-fidelity report cards for your students." accent="results">
            <x-slot:actions>
                <a href="{{ route('examination') }}" class="btn-outline">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back to Exams
                </a>
            </x-slot:actions>
        </x-page-header>

        @if (session('status'))
            <div class="card-padded border border-green-200 bg-green-50/60 text-sm text-green-900 rounded-3xl">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="card-padded border border-orange-200 bg-orange-50/60 rounded-3xl">
                <div class="text-sm font-semibold text-orange-900">Please fix the following:</div>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-orange-900">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            {{-- Configuration Sidebar --}}
            <div class="xl:col-span-1 space-y-8">
                <div class="rounded-[2.5rem] border border-gray-100 bg-white p-8 shadow-2xl shadow-indigo-100/50">
                    <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center gap-2">
                        <span class="w-1.5 h-6 bg-indigo-600 rounded-full"></span>
                        Configuration
                    </h3>

                    <form method="POST" action="{{ route('results.bulk-report-cards.generate') }}" id="bulkForm" class="space-y-6" @submit="generating = true">
                        @csrf
                        
                        <div class="space-y-4">
                            <div>
                                <label class="text-xs font-black uppercase tracking-[0.1em] text-gray-500">Select Class</label>
                                <select name="class_id" x-model="classId" @change="fetchStudents()" class="mt-2 w-full rounded-2xl border-2 border-gray-50 bg-gray-50 px-5 py-4 text-sm font-bold text-gray-900 transition-all focus:border-indigo-600 focus:bg-white focus:ring-0" required>
                                    <option value="">Select class</option>
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}" @selected(old('class_id') == $class->id)>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-xs font-black uppercase tracking-[0.1em] text-gray-500">Session</label>
                                    <input name="session" type="text" x-model="session" class="mt-2 w-full rounded-2xl border-2 border-gray-50 bg-gray-50 px-5 py-4 text-sm font-bold text-gray-900 transition-all focus:border-indigo-600 focus:bg-white focus:ring-0" required />
                                </div>
                                <div>
                                    <label class="text-xs font-black uppercase tracking-[0.1em] text-gray-500">Term</label>
                                    <select name="term" x-model="term" class="mt-2 w-full rounded-2xl border-2 border-gray-50 bg-gray-50 px-5 py-4 text-sm font-bold text-gray-900 transition-all focus:border-indigo-600 focus:bg-white focus:ring-0" required>
                                        <option value="1">Term 1</option>
                                        <option value="2">Term 2</option>
                                        <option value="3">Term 3</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="text-xs font-black uppercase tracking-[0.1em] text-gray-500">Visual Template</label>
                                <select name="template" x-model="template" class="mt-2 w-full rounded-2xl border-2 border-gray-50 bg-gray-50 px-5 py-4 text-sm font-bold text-gray-900 transition-all focus:border-indigo-600 focus:bg-white focus:ring-0">
                                    <option value="">Default Setting</option>
                                    <option value="standard">Standard</option>
                                    <option value="compact">Compact</option>
                                    <option value="elegant">Elegant</option>
                                    <option value="modern">Modern</option>
                                    <option value="classic">Classic</option>
                                    <option value="vibrant">Vibrant</option>
                                    <option value="professional">Professional</option>
                                    <option value="royal">Royal</option>
                                    <option value="fresh">Fresh</option>
                                    <option value="sunset">Sunset</option>
                                </select>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-gray-100">
                            <h4 class="text-xs font-black uppercase tracking-[0.1em] text-gray-500 mb-4">Display Options</h4>
                            <div class="space-y-3">
                                @foreach([
                                    'show_psychomotor' => 'Psychomotor Traits',
                                    'show_attendance' => 'Attendance Summary',
                                    'show_position' => 'Student Rank/Position',
                                    'show_class_average' => 'Class Statistics'
                                ] as $opt => $label)
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <div class="relative flex items-center">
                                            <input type="checkbox" name="options[{{ $opt }}]" value="1" checked 
                                                class="peer h-6 w-6 rounded-lg border-2 border-gray-200 text-indigo-600 focus:ring-0 transition-all checked:border-indigo-600">
                                            <svg class="absolute w-4 h-4 text-white left-1 opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="4">
                                                <path d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <span class="text-sm font-bold text-gray-600 group-hover:text-indigo-600 transition-colors">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="pt-8">
                            <button type="submit" 
                                :disabled="selectedStudents.length === 0"
                                class="w-full rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 py-4 text-sm font-black text-white shadow-xl shadow-indigo-100 transition-all hover:shadow-indigo-200 active:scale-[0.98] disabled:opacity-50 disabled:grayscale">
                                <span class="flex items-center justify-center gap-2">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                        <polyline points="7 10 12 15 17 10"/>
                                        <line x1="12" y1="15" x2="12" y2="3"/>
                                    </svg>
                                    GENERATE ZIP (<span x-text="selectedStudents.length"></span>)
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Student Selection Pane --}}
            <div class="xl:col-span-2">
                <div class="rounded-[2.5rem] border border-gray-100 bg-white shadow-2xl shadow-indigo-100/50 overflow-hidden h-full flex flex-col min-h-[600px]">
                    <div class="bg-gray-50 border-b border-gray-100 px-8 py-6 flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-black text-gray-900">Student Selection</h3>
                            <p class="text-sm text-gray-500 font-bold">Select which students to include in the batch.</p>
                        </div>
                        <template x-if="students.length > 0">
                            <button @click="toggleAll()" type="button" class="text-xs font-black uppercase tracking-widest text-indigo-600 hover:text-indigo-800 transition-colors">
                                <span x-text="selectedStudents.length === students.length ? 'Deselect All' : 'Select All'"></span>
                            </button>
                        </template>
                    </div>

                    <div class="flex-1 overflow-y-auto p-8">
                        {{-- Loading State --}}
                        <div x-show="loading" class="h-full flex flex-col items-center justify-center py-20">
                            <div class="w-12 h-12 border-4 border-indigo-100 border-t-indigo-600 rounded-full animate-spin mb-4"></div>
                            <p class="text-sm font-black text-gray-400">Fetching class roster...</p>
                        </div>

                        {{-- Empty/Initial State --}}
                        <div x-show="!loading && students.length === 0" class="h-full flex flex-col items-center justify-center py-20 text-center">
                            <div class="w-24 h-24 bg-gray-50 rounded-[2rem] flex items-center justify-center mb-6">
                                <svg class="w-12 h-12 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-black text-gray-900 mb-2">No Class Selected</h4>
                            <p class="text-sm font-bold text-gray-400 max-w-xs">Select a class from the configuration pane to load the student list.</p>
                        </div>

                        {{-- Students Grid --}}
                        <div x-show="!loading && students.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-2 gap-4">
                            <template x-for="student in students" :key="student.id">
                                <div class="relative flex items-center gap-4 rounded-2xl border-2 p-4 transition-all hover:border-indigo-200"
                                    :class="selectedStudents.includes(student.id) ? 'border-indigo-100 bg-indigo-50/30 shadow-inner' : 'border-gray-50 bg-white shadow-sm'">
                                    
                                    <div class="relative flex items-center">
                                        <input type="checkbox" name="student_ids[]" :value="student.id" form="bulkForm"
                                            x-model="selectedStudents"
                                            class="peer h-6 w-6 rounded-lg border-2 border-gray-200 text-indigo-600 focus:ring-0 transition-all checked:border-indigo-600 cursor-pointer">
                                        <svg class="absolute w-4 h-4 text-white left-1 opacity-0 peer-checked:opacity-100 transition-opacity pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="4">
                                            <path d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>

                                    <div class="flex-1 overflow-hidden">
                                        <p class="text-sm font-black text-gray-900 truncate" x-text="`${student.first_name} ${student.last_name}`"></p>
                                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest" x-text="student.admission_number"></p>
                                    </div>

                                    <button @click="preview(student.id)" type="button" 
                                        class="p-2 rounded-xl bg-white text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 border border-gray-100 shadow-sm transition-all active:scale-95">
                                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Centered Modal Loader --}}
        <div x-show="generating" 
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            style="display: none;">
            <div class="bg-white rounded-[2.5rem] p-10 max-w-sm w-full mx-4 shadow-2xl flex flex-col items-center text-center border border-gray-50 transform transition-all"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="scale-95 translate-y-4"
                x-transition:enter-end="scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="scale-100 translate-y-0"
                x-transition:leave-end="scale-95 translate-y-4">
                <div class="relative w-20 h-20 mb-6 flex items-center justify-center">
                    <div class="absolute inset-0 border-4 border-indigo-50 rounded-full"></div>
                    <div class="absolute inset-0 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                    <div class="w-6 h-6 bg-indigo-100 rounded-full animate-ping opacity-75"></div>
                </div>
                <h3 class="text-xl font-black text-gray-900 mb-2">Generating Report Cards</h3>
                <p class="text-sm font-semibold text-gray-500 mb-6 leading-relaxed">
                    We are compiling student records, rendering visual templates, and archiving PDFs into a ZIP file.
                </p>
                <div class="flex flex-col gap-2 w-full">
                    <div class="text-[10px] font-black uppercase tracking-widest text-indigo-600 animate-pulse mb-2">
                        Processing, please wait...
                    </div>
                    <button @click="generating = false" type="button" 
                        class="w-full rounded-2xl bg-gray-50 hover:bg-gray-100 py-3 text-xs font-bold text-gray-700 transition-colors focus:outline-none">
                        Close Loader
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
