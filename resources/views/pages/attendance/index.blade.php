@php
    use App\Models\SchoolClass;
    use App\Models\Section;

    $classes = SchoolClass::query()->orderBy('level')->get();
    $sections = Section::query()->orderBy('name')->get();
@endphp

@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-page-header
            title="Attendance"
            subtitle="Modern attendance UI (module setup is pending in this build)."
            accent="attendance"
        />

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="card-padded lg:col-span-2">
                <div class="text-sm font-semibold text-gray-900">Quick Take</div>
                <div class="mt-2 text-sm text-gray-600">
                    Choose a class and section to start taking attendance.
                </div>

                <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Class</label>
                        <select class="mt-2 select" disabled>
                            <option>Choose class</option>
                            @foreach ($classes as $class)
                                <option>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold uppercase tracking-wider text-gray-500">Section</label>
                        <select class="mt-2 select" disabled>
                            <option>Choose section</option>
                            @foreach ($sections as $section)
                                <option>{{ $section->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap gap-3">
                    <button type="button" class="btn-primary opacity-60" disabled>Start Attendance</button>
                    <button type="button" class="btn-outline opacity-60" disabled>View History</button>
                </div>

                <div class="mt-4 rounded-2xl border border-brand-100 bg-brand-50 p-4 text-sm text-brand-800">
                    This module needs an attendance table + logic. The UI is ready for integration.
                </div>
            </div>

            <div class="card-padded">
                <div class="text-sm font-semibold text-gray-900">Tips</div>
                <div class="mt-4 space-y-3 text-sm text-gray-600">
                    <div class="rounded-2xl bg-gray-50 p-4 ring-1 ring-inset ring-gray-100">
                        Use a tablet for faster marking.
                    </div>
                    <div class="rounded-2xl bg-gray-50 p-4 ring-1 ring-inset ring-gray-100">
                        Add a “Late” option for schools that track punctuality.
                    </div>
                    <div class="rounded-2xl bg-gray-50 p-4 ring-1 ring-inset ring-gray-100">
                        Print daily summary for the principal.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
