@extends('layouts.app')

@section('content')
@php
    $hasPremium = true;
    $isCertLocked = false;
    $certificateTemplate = old('certificate_template', config('academyhub.certificate_template', 'modern'));
    $certificateTemplatesData = [
        ['key' => 'modern',   'title' => 'Modern',   'desc' => 'Clean and contemporary design.',       'preview' => route('settings.templates.preview', ['type' => 'certificate', 'template' => 'modern']),   'free' => true],
        ['key' => 'classic',  'title' => 'Classic',  'desc' => 'Traditional and formal appearance.',   'preview' => route('settings.templates.preview', ['type' => 'certificate', 'template' => 'classic']),  'free' => true],
        ['key' => 'elegant',  'title' => 'Elegant',  'desc' => 'Refined design with ornate elements.', 'preview' => route('settings.templates.preview', ['type' => 'certificate', 'template' => 'elegant']),  'free' => true],
        ['key' => 'vibrant',  'title' => 'Vibrant',  'desc' => 'Colorful and energetic aesthetic.',    'preview' => route('settings.templates.preview', ['type' => 'certificate', 'template' => 'vibrant']),  'free' => false],
        ['key' => 'minimal',  'title' => 'Minimal',  'desc' => 'Simple, structured, and uncluttered.', 'preview' => route('settings.templates.preview', ['type' => 'certificate', 'template' => 'minimal']),  'free' => false],
        ['key' => 'royal',    'title' => 'Royal',    'desc' => 'Luxurious and prestigious styling.',   'preview' => route('settings.templates.preview', ['type' => 'certificate', 'template' => 'royal']),    'free' => false],
        ['key' => 'obsidian', 'title' => 'Obsidian', 'desc' => 'Dark and striking aesthetic.',         'preview' => route('settings.templates.preview', ['type' => 'certificate', 'template' => 'obsidian']), 'free' => false],
        ['key' => 'sahara',   'title' => 'Sahara',   'desc' => 'Warm and vibrant tones.',              'preview' => route('settings.templates.preview', ['type' => 'certificate', 'template' => 'sahara']),   'free' => false],
        ['key' => 'oceanic',  'title' => 'Oceanic',  'desc' => 'Cool blue and wavy accents.',          'preview' => route('settings.templates.preview', ['type' => 'certificate', 'template' => 'oceanic']),  'free' => false],
        ['key' => 'crimson',  'title' => 'Crimson',  'desc' => 'Bold red and professional.',           'preview' => route('settings.templates.preview', ['type' => 'certificate', 'template' => 'crimson']),  'free' => false],
        ['key' => 'ivory',    'title' => 'Ivory',    'desc' => 'Sophisticated pale tones.',            'preview' => route('settings.templates.preview', ['type' => 'certificate', 'template' => 'ivory']),    'free' => false],
    ];
@endphp

<div class="space-y-6" x-data="{ open: false, src: null, title: '', selectedCertificateTemplate: '{{ $certificateTemplate }}' }">

    <x-page-header title="Certificate Settings" subtitle="Customize certificate design and templates." accent="settings" />

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

    <form method="POST" action="{{ route('settings.update-certificates') }}" enctype="multipart/form-data">
        @csrf
        <div class="space-y-6">

            {{-- Design Options --}}
            <div class="rounded-3xl border border-gray-100 bg-gradient-to-br from-amber-50 to-rose-50/60 p-6 shadow-lg">
                <div class="flex items-center gap-3 mb-5">
                    <div class="icon-3d grid h-12 w-12 place-items-center rounded-xl bg-gradient-to-br from-amber-500 to-rose-600 text-white shadow-lg shadow-amber-500/30">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M6 2h9l3 3v15a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z" />
                            <path d="M14 2v4h4" /><path d="M8 13h8" /><path d="M8 17h6" />
                        </svg>
                    </div>
                    <div class="text-lg font-black text-gray-900">Certificate Design</div>
                </div>

                <div class="space-y-6">
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-gray-700">Orientation</label>
                            @php($orientation = old('certificate_orientation', config('academyhub.certificate_orientation', 'landscape')))
                            <select name="certificate_orientation" class="mt-2 w-full rounded-xl border-0 bg-white/80 px-4 py-3 text-sm font-semibold text-gray-900 ring-1 ring-white/60 backdrop-blur-sm focus:ring-2 focus:ring-amber-500">
                                <option value="landscape" @selected($orientation === 'landscape')>Landscape</option>
                                <option value="portrait" @selected($orientation === 'portrait')>Portrait</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-gray-700">Border color</label>
                            <input name="certificate_border_color" type="color"
                                class="mt-2 h-12 w-full rounded-xl border-0 bg-white/80 p-1 ring-1 ring-white/60 backdrop-blur-sm focus:ring-2 focus:ring-amber-500"
                                value="{{ old('certificate_border_color', config('academyhub.certificate_border_color', '#0ea5e9')) }}" required />
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase tracking-wider text-gray-700">Accent color</label>
                            <input name="certificate_accent_color" type="color"
                                class="mt-2 h-12 w-full rounded-xl border-0 bg-white/80 p-1 ring-1 ring-white/60 backdrop-blur-sm focus:ring-2 focus:ring-amber-500"
                                value="{{ old('certificate_accent_color', config('academyhub.certificate_accent_color', '#0ea5e9')) }}" required />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                        <div>
                            <input type="hidden" name="certificate_show_logo" value="0" />
                            <label class="flex items-center gap-3 text-sm font-semibold text-gray-700">
                                <input type="checkbox" name="certificate_show_logo" value="1"
                                    class="h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500"
                                    @checked(old('certificate_show_logo', (bool) config('academyhub.certificate_show_logo', true))) />
                                Show school logo
                            </label>
                        </div>
                        <div>
                            <input type="hidden" name="certificate_show_watermark" value="0" />
                            <label class="flex items-center gap-3 text-sm font-semibold text-gray-700">
                                <input type="checkbox" name="certificate_show_watermark" value="1"
                                    class="h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500"
                                    @checked(old('certificate_show_watermark', (bool) config('academyhub.certificate_show_watermark', false))) />
                                Show watermark image (if uploaded)
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-bold uppercase tracking-wider text-gray-700">Watermark image (optional)</label>
                        <input name="certificate_watermark_image" type="file" accept="image/*"
                            class="mt-2 w-full rounded-xl border-0 bg-white/80 px-4 py-3 text-sm font-semibold text-gray-900 ring-1 ring-white/60 backdrop-blur-sm focus:ring-2 focus:ring-amber-500" />
                        <input type="hidden" name="certificate_watermark_remove" value="0" />
                        @if(config('academyhub.certificate_watermark_image'))
                            <div class="mt-3 flex items-center justify-between gap-3 rounded-2xl border border-white/70 bg-white/60 p-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <img src="{{ asset('uploads/' . str_replace('\\', '/', config('academyhub.certificate_watermark_image'))) }}"
                                        alt="Watermark" class="h-12 w-12 rounded-xl bg-white object-contain p-2 ring-1 ring-white/60" />
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold uppercase tracking-wider text-gray-600">Current watermark</div>
                                        <div class="mt-1 truncate text-xs text-gray-600">{{ basename(config('academyhub.certificate_watermark_image')) }}</div>
                                    </div>
                                </div>
                                <label class="flex items-center gap-2 text-xs font-semibold text-gray-700">
                                    <input type="checkbox" name="certificate_watermark_remove" value="1"
                                        class="h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500" />
                                    Remove
                                </label>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Signatures --}}
            <div class="rounded-3xl border border-gray-100 bg-gradient-to-br from-amber-50 to-rose-50/60 p-6 shadow-lg">
                <div class="text-sm font-black text-gray-900 mb-4">Signatures</div>
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

                    {{-- Signature 1 --}}
                    <div class="space-y-3">
                        <div class="text-xs font-bold uppercase tracking-wider text-gray-600">Signature 1</div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Label</label>
                                <input name="certificate_signature_label"
                                    class="mt-2 w-full rounded-xl border-0 bg-white/80 px-4 py-3 text-sm font-semibold text-gray-900 ring-1 ring-white/60 backdrop-blur-sm focus:ring-2 focus:ring-amber-500"
                                    value="{{ old('certificate_signature_label', config('academyhub.certificate_signature_label', 'Authorized Signature')) }}" />
                            </div>
                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Name (optional)</label>
                                <input name="certificate_signature_name"
                                    class="mt-2 w-full rounded-xl border-0 bg-white/80 px-4 py-3 text-sm font-semibold text-gray-900 ring-1 ring-white/60 backdrop-blur-sm focus:ring-2 focus:ring-amber-500"
                                    value="{{ old('certificate_signature_name', config('academyhub.certificate_signature_name')) }}" />
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Signature image (optional)</label>
                            <input name="certificate_signature_image" type="file" accept="image/*"
                                class="mt-2 w-full rounded-xl border-0 bg-white/80 px-4 py-3 text-sm font-semibold text-gray-900 ring-1 ring-white/60 backdrop-blur-sm focus:ring-2 focus:ring-amber-500" />
                            <input type="hidden" name="certificate_signature_remove" value="0" />
                            @if(config('academyhub.certificate_signature_image'))
                                <div class="mt-3 flex items-center justify-between gap-3 rounded-2xl border border-white/70 bg-white/60 p-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <img src="{{ asset('uploads/' . str_replace('\\', '/', config('academyhub.certificate_signature_image'))) }}"
                                            alt="Signature" class="h-12 w-24 rounded-xl bg-white object-contain p-2 ring-1 ring-white/60" />
                                        <div class="min-w-0">
                                            <div class="text-xs font-bold uppercase tracking-wider text-gray-600">Current signature</div>
                                            <div class="mt-1 truncate text-xs text-gray-600">{{ basename(config('academyhub.certificate_signature_image')) }}</div>
                                        </div>
                                    </div>
                                    <label class="flex items-center gap-2 text-xs font-semibold text-gray-700">
                                        <input type="checkbox" name="certificate_signature_remove" value="1"
                                            class="h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500" />
                                        Remove
                                    </label>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Signature 2 --}}
                    <div class="space-y-3">
                        <div class="text-xs font-bold uppercase tracking-wider text-gray-600">Signature 2 (optional)</div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Label</label>
                                <input name="certificate_signature2_label" placeholder="e.g. Registrar"
                                    class="mt-2 w-full rounded-xl border-0 bg-white/80 px-4 py-3 text-sm font-semibold text-gray-900 ring-1 ring-white/60 backdrop-blur-sm focus:ring-2 focus:ring-amber-500"
                                    value="{{ old('certificate_signature2_label', config('academyhub.certificate_signature2_label')) }}" />
                            </div>
                            <div>
                                <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Name (optional)</label>
                                <input name="certificate_signature2_name"
                                    class="mt-2 w-full rounded-xl border-0 bg-white/80 px-4 py-3 text-sm font-semibold text-gray-900 ring-1 ring-white/60 backdrop-blur-sm focus:ring-2 focus:ring-amber-500"
                                    value="{{ old('certificate_signature2_name', config('academyhub.certificate_signature2_name')) }}" />
                            </div>
                        </div>
                        <div>
                            <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Signature image (optional)</label>
                            <input name="certificate_signature2_image" type="file" accept="image/*"
                                class="mt-2 w-full rounded-xl border-0 bg-white/80 px-4 py-3 text-sm font-semibold text-gray-900 ring-1 ring-white/60 backdrop-blur-sm focus:ring-2 focus:ring-amber-500" />
                            <input type="hidden" name="certificate_signature2_remove" value="0" />
                            @if(config('academyhub.certificate_signature2_image'))
                                <div class="mt-3 flex items-center justify-between gap-3 rounded-2xl border border-white/70 bg-white/60 p-3">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <img src="{{ asset('uploads/' . str_replace('\\', '/', config('academyhub.certificate_signature2_image'))) }}"
                                            alt="Signature 2" class="h-12 w-24 rounded-xl bg-white object-contain p-2 ring-1 ring-white/60" />
                                        <div class="min-w-0">
                                            <div class="text-xs font-bold uppercase tracking-wider text-gray-600">Current signature</div>
                                            <div class="mt-1 truncate text-xs text-gray-600">{{ basename(config('academyhub.certificate_signature2_image')) }}</div>
                                        </div>
                                    </div>
                                    <label class="flex items-center gap-2 text-xs font-semibold text-gray-700">
                                        <input type="checkbox" name="certificate_signature2_remove" value="1"
                                            class="h-4 w-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500" />
                                        Remove
                                    </label>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

            {{-- Default Template Content --}}
            <div class="rounded-3xl border border-gray-100 bg-gradient-to-br from-amber-50 to-rose-50/60 p-6 shadow-lg">
                <div class="text-sm font-black text-gray-900">Default Certificate Content</div>
                <div class="mt-1 text-sm font-semibold text-gray-600">Used as the starting template when creating new certificates.</div>
                <div class="mt-4 grid grid-cols-1 gap-4 lg:grid-cols-3">
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Default type</label>
                        <input name="certificate_default_type"
                            class="mt-2 w-full rounded-xl border-0 bg-white/80 px-4 py-3 text-sm font-semibold text-gray-900 ring-1 ring-white/60 backdrop-blur-sm focus:ring-2 focus:ring-amber-500"
                            value="{{ old('certificate_default_type', config('academyhub.certificate_default_type', 'General')) }}" />
                    </div>
                    <div class="lg:col-span-2">
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Default title</label>
                        <input name="certificate_default_title"
                            class="mt-2 w-full rounded-xl border-0 bg-white/80 px-4 py-3 text-sm font-semibold text-gray-900 ring-1 ring-white/60 backdrop-blur-sm focus:ring-2 focus:ring-amber-500"
                            value="{{ old('certificate_default_title', config('academyhub.certificate_default_title', 'Certificate')) }}" />
                    </div>
                </div>
                <div class="mt-4">
                    <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Default body</label>
                    <textarea name="certificate_default_body" rows="5"
                        class="mt-2 w-full rounded-xl border-0 bg-white/80 px-4 py-3 text-sm font-semibold text-gray-900 ring-1 ring-white/60 backdrop-blur-sm focus:ring-2 focus:ring-amber-500">{{ old('certificate_default_body', config('academyhub.certificate_default_body')) }}</textarea>
                    <div class="mt-2 text-xs text-gray-600">
                        Placeholders: <span class="font-mono">{student_name}</span>, <span class="font-mono">{admission_number}</span>,
                        <span class="font-mono">{class}</span>, <span class="font-mono">{section}</span>,
                        <span class="font-mono">{issue_date}</span>, <span class="font-mono">{school_name}</span>
                    </div>
                </div>
            </div>

            {{-- Template Picker --}}
            <div class="rounded-3xl border border-gray-100 bg-gradient-to-br from-amber-50 to-rose-50/60 p-6 shadow-lg">
                <div class="text-sm font-black text-gray-900">Certificate Template</div>
                <div class="mt-1 text-sm font-semibold text-gray-600">Choose your preferred certificate design.</div>
                <div class="mt-4 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    <?php foreach ($certificateTemplatesData as $ct): ?>
                        @php
                            $isCertLocked = !$ct['free'] && !$hasPremium;
                        @endphp
                        <label
                            :class="selectedCertificateTemplate === '{{ $ct['key'] }}' ? 'border-amber-400 ring-2 ring-amber-600 bg-amber-50/20' : 'border-white/60'"
                            class="group flex h-full cursor-pointer flex-col rounded-2xl border bg-white/60 p-5 shadow-sm ring-1 ring-white/50 backdrop-blur transition hover:border-amber-200 hover:shadow-md {{ $isCertLocked ? 'opacity-60' : '' }}">
                            <input type="radio" name="certificate_template" value="{{ $ct['key'] }}" class="sr-only"
                                x-model="selectedCertificateTemplate"
                                @checked($certificateTemplate === $ct['key'])
                                @disabled($isCertLocked) />
                            <div class="relative mb-4 w-full overflow-hidden rounded-xl border border-gray-200 bg-white pointer-events-none select-none" style="aspect-ratio: 1.414 / 1;">
                                <div class="absolute inset-0" style="width:200%;height:200%;transform:scale(0.5);transform-origin:top left;">
                                    <iframe src="{{ $ct['preview'] }}?html=1" class="w-full h-full border-0 bg-transparent" scrolling="no" tabindex="-1"></iframe>
                                </div>
                            </div>
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5 text-[13px] font-black text-gray-900">
                                        {{ $ct['title'] }}
                                        @if($isCertLocked)
                                            <svg class="h-3 w-3 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                        @endif
                                    </div>
                                    <div class="mt-1 line-clamp-2 text-[11px] font-semibold text-gray-600">{{ $ct['desc'] }}</div>
                                </div>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[9px] font-black {{ $ct['free'] ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $ct['free'] ? 'FREE' : 'PRO' }}
                                </span>
                            </div>
                            <div class="mt-auto flex items-center justify-between pt-3">
                                <div class="text-[9px] font-semibold uppercase tracking-wider text-gray-500">{{ $isCertLocked ? 'Locked' : 'Select' }}</div>
                                <button type="button"
                                    class="rounded-lg border border-slate-200 bg-white/80 px-2 py-1 text-[10px] font-bold text-slate-700 transition-colors hover:bg-white"
                                    @click.stop="open = true; src = @js($ct['preview']); title = @js('Certificate · ' . $ct['title'])">
                                    Full Preview
                                </button>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="submit" class="w-full rounded-xl bg-amber-600 px-5 py-3 text-sm font-bold text-white shadow-lg transition-all hover:bg-amber-700">
                Save Certificate Settings
            </button>

        </div>
    </form>

    {{-- Preview Modal --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 p-4" @click.self="open = false">
        <div class="w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-black/5">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div class="text-sm font-black text-slate-900" x-text="title"></div>
                <button type="button" class="rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-200" @click="open = false">Close</button>
            </div>
            <div class="h-[80vh] bg-slate-50">
                <iframe :src="src" class="h-full w-full border-0" title="Template Preview"></iframe>
            </div>
        </div>
    </div>

</div>
@endsection
