@php
    $user = auth()->user();
    $children = $user->students()->with(['schoolClass', 'section'])->get();
@endphp

@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-page-header title="Parent Portal" subtitle="Welcome back, {{ $user->name }}. View your children's progress here." accent="indigo" />

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($children as $child)
                <div class="card-padded hover:shadow-lg transition-shadow duration-200">
                    <div class="flex items-center gap-4 mb-4">
                        @if($child->passport_photo_url)
                            <img src="{{ $child->passport_photo_url }}" class="h-16 w-16 rounded-full object-cover shadow-sm bg-gray-100" />
                        @else
                            <div class="h-16 w-16 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-xl shadow-sm">
                                {{ substr($child->first_name, 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <h3 class="font-bold text-lg text-gray-900">{{ $child->full_name }}</h3>
                            <p class="text-sm text-gray-500">{{ $child->admission_number }}</p>
                            <span class="inline-flex mt-1 items-center gap-1.5 py-1 px-2.5 rounded-md text-xs font-medium bg-gray-100 text-gray-700">
                                {{ $child->schoolClass?->name ?? 'Unassigned' }} {{ $child->section?->name ?? '' }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 mt-6 pt-4 border-t border-gray-100">
                        <!-- Quick Actions for Child -->
                        <a href="{{ route('results.report-card', $child) }}" target="_blank" class="btn-outline justify-center text-sm py-2">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Result PDF
                        </a>
                        <!-- Future: link to individual fee tracking -->
                        <button type="button" class="btn-outline justify-center text-sm py-2 opacity-50 cursor-not-allowed">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Fee Status
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full card-padded text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    <h3 class="mt-4 text-sm font-semibold text-gray-900">No children linked</h3>
                    <p class="mt-1 text-sm text-gray-500">Please contact the school administrator to link your children to this account.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
