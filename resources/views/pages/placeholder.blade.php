@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-page-header
            :title="$pageTitle ?? 'Page'"
            subtitle="This module is being built. The UI shell is ready for customization."
            accent="more"
        />

        <div class="card-padded text-center">
            <div class="mx-auto grid h-16 w-16 place-items-center rounded-2xl bg-gray-100 text-gray-700 ring-1 ring-inset ring-gray-200">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 2v6" />
                    <path d="M12 18v4" />
                    <path d="M4.93 4.93l4.24 4.24" />
                    <path d="M14.83 14.83l4.24 4.24" />
                    <path d="M2 12h6" />
                    <path d="M16 12h6" />
                    <path d="M4.93 19.07l4.24-4.24" />
                    <path d="M14.83 9.17l4.24-4.24" />
                </svg>
            </div>
            <div class="mt-4 text-sm font-semibold text-gray-900">Coming soon</div>
            <div class="mt-2 text-sm text-gray-600">
                Tell me what you want this page to do (fields, buttons, table columns), and I’ll implement it.
            </div>
        </div>
    </div>
@endsection
