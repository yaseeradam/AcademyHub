<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Academic Result Verification | {{ $schoolName ?? config('app.name', 'AcademyHub') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="min-h-full flex flex-col justify-center items-center py-8 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-lg">
        {{-- Verification Card Container --}}
        <div class="bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden">
            
            {{-- Header Ribbon --}}
            <div class="bg-gradient-to-r from-blue-900 to-indigo-900 text-white px-6 py-5 text-center relative">
                <div class="flex items-center justify-center space-x-3">
                    @if (!empty($schoolLogo))
                        <img src="{{ asset('uploads/' . str_replace('\\', '/', $schoolLogo)) }}" alt="Logo" class="w-12 h-12 rounded-full object-contain bg-white/10 p-1 border border-white/20">
                    @else
                        <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center text-lg font-bold border border-white/30">
                            {{ strtoupper(substr($schoolName ?? 'A', 0, 1)) }}
                        </div>
                    @endif
                    <div class="text-left">
                        <h1 class="text-lg font-extrabold uppercase tracking-tight text-white leading-tight">{{ $schoolName ?? 'AcademyHub' }}</h1>
                        <p class="text-xs text-indigo-200 italic">{{ $schoolMotto ?? 'Excellence and Integrity' }}</p>
                    </div>
                </div>
            </div>

            <div class="p-6 sm:p-8">
                @if ($found)
                    {{-- Status Banner: Verified --}}
                    <div class="flex items-center space-x-3 p-4 mb-6 bg-emerald-50 border border-emerald-200 rounded-xl">
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Authentic & Verified Record</div>
                            <div class="text-sm font-semibold text-emerald-950">This academic credential is authentic and validated.</div>
                        </div>
                    </div>

                    {{-- Student Identity Info --}}
                    <div class="space-y-4">
                        <div class="flex items-center space-x-4 pb-4 border-b border-slate-100">
                            @if ($student->photo)
                                <img src="{{ asset('uploads/' . str_replace('\\', '/', $student->photo)) }}" alt="Student" class="w-16 h-16 rounded-full object-cover border-2 border-indigo-100 shadow-sm">
                            @else
                                <div class="w-16 h-16 rounded-full bg-slate-100 text-slate-500 font-bold text-xl flex items-center justify-center border-2 border-slate-200 shadow-sm">
                                    {{ strtoupper(substr($student->first_name ?? 'S', 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <h2 class="text-xl font-bold text-slate-900 leading-snug">
                                    {{ trim("{$student->first_name} {$student->middle_name} {$student->last_name}") }}
                                </h2>
                                <p class="text-sm font-mono text-indigo-600 font-semibold">{{ $student->admission_number }}</p>
                            </div>
                        </div>

                        {{-- Academic Meta Grid --}}
                        <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                            <div class="bg-slate-50 p-3 rounded-lg border border-slate-100">
                                <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">Class & Section</dt>
                                <dd class="mt-1 font-bold text-slate-800">
                                    {{ $student->schoolClass?->name ?? 'N/A' }}{{ $student->section?->name ? ' - ' . $student->section->name : '' }}
                                </dd>
                            </div>

                            <div class="bg-slate-50 p-3 rounded-lg border border-slate-100">
                                <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">Academic Session</dt>
                                <dd class="mt-1 font-bold text-slate-800">{{ $session }}</dd>
                            </div>

                            <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 col-span-2">
                                <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">Published Academic Terms</dt>
                                <dd class="mt-1 font-semibold text-slate-800 flex items-center space-x-2">
                                    @if (!empty($publishedTerms))
                                        @foreach ($publishedTerms as $termNum)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-indigo-100 text-indigo-700">
                                                Term {{ $termNum }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="text-amber-600 text-xs italic">Awaiting Term Publication</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>

                        {{-- Digital Seal --}}
                        <div class="mt-6 p-3 bg-indigo-50/60 rounded-xl border border-indigo-100/80 flex items-center justify-between">
                            <div>
                                <div class="text-[10px] font-bold text-indigo-500 uppercase tracking-wider">Anti-Forgery Digital Seal</div>
                                <div class="font-mono text-xs font-bold text-indigo-900">{{ $digitalSeal }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-[10px] font-bold text-indigo-500 uppercase tracking-wider">Verified Timestamp</div>
                                <div class="text-xs text-indigo-900 font-semibold">{{ $verifiedAt->format('d M Y, h:i A') }}</div>
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Status Banner: Not Found --}}
                    <div class="text-center py-6">
                        <div class="w-16 h-16 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h2 class="text-lg font-extrabold text-slate-900 mb-1">Record Not Found</h2>
                        <p class="text-sm text-slate-600 max-w-sm mx-auto mb-4">
                            No active student record could be found matching admission number
                            <strong class="font-mono text-slate-800">{{ $admissionNumber }}</strong>.
                        </p>
                        <p class="text-xs text-slate-400">
                            Please inspect the printed document or contact the school administrative office for verification assistance.
                        </p>
                    </div>
                @endif
            </div>

            {{-- Footer Note --}}
            <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 text-center">
                <p class="text-[11px] text-slate-400">
                    Official Document Verification Service • Powered by <span class="font-semibold text-slate-600">AcademyHub</span>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
