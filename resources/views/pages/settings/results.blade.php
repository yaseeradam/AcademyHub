@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-page-header title="Result Scoring" subtitle="Configure maximum marks for CA and Exam." accent="settings" />

        <div class="flex gap-2">
            <a href="{{ route('settings.index') }}" class="btn-outline">← Back to Settings</a>
        </div>

        @if (session('status'))
            <div class="card-padded border border-green-200 bg-green-50/60 text-sm text-green-900">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="card-padded border border-orange-200 bg-orange-50/60">
                <div class="text-sm font-semibold text-orange-900">Please fix the following:</div>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-orange-900">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-3xl border border-gray-100 bg-gradient-to-br from-purple-50 to-fuchsia-50/60 p-6 shadow-lg max-w-2xl">
            <div class="flex items-center gap-3 mb-5">
                <div class="icon-3d grid h-12 w-12 place-items-center rounded-xl bg-gradient-to-br from-purple-500 to-fuchsia-600 text-white shadow-lg shadow-purple-500/30">
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M9 11l3 3L22 4" />
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                    </svg>
                </div>
                <div class="text-lg font-black text-gray-900">Maximum Marks Configuration</div>
            </div>

            <form method="POST" action="{{ route('settings.update-results') }}">
                @csrf
                <div class="space-y-4">
                    <div class="text-sm font-semibold text-gray-600">
                        Set the maximum marks for CA and Exam (used for validation and grading).
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-gray-700">CA1 Max</label>
                            <input
                                name="results_ca1_max"
                                type="number"
                                min="0"
                                max="200"
                                class="mt-2 w-full rounded-xl border-0 bg-white/80 px-4 py-3 text-sm font-semibold text-gray-900 ring-1 ring-white/60 backdrop-blur-sm focus:ring-2 focus:ring-purple-500"
                                value="{{ old('results_ca1_max', config('academyhub.results_ca1_max', 20)) }}"
                                required
                            />
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-gray-700">CA2 Max</label>
                            <input
                                name="results_ca2_max"
                                type="number"
                                min="0"
                                max="200"
                                class="mt-2 w-full rounded-xl border-0 bg-white/80 px-4 py-3 text-sm font-semibold text-gray-900 ring-1 ring-white/60 backdrop-blur-sm focus:ring-2 focus:ring-purple-500"
                                value="{{ old('results_ca2_max', config('academyhub.results_ca2_max', 20)) }}"
                                required
                            />
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-gray-700">Exam Max</label>
                            <input
                                name="results_exam_max"
                                type="number"
                                min="0"
                                max="200"
                                class="mt-2 w-full rounded-xl border-0 bg-white/80 px-4 py-3 text-sm font-semibold text-gray-900 ring-1 ring-white/60 backdrop-blur-sm focus:ring-2 focus:ring-purple-500"
                                value="{{ old('results_exam_max', config('academyhub.results_exam_max', 60)) }}"
                                required
                            />
                        </div>
                    </div>

                    <button type="submit" class="w-full rounded-xl bg-purple-600 px-5 py-3 text-sm font-bold text-white shadow-lg hover:bg-purple-700 transition-all">
                        Save Scoring Marks
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
