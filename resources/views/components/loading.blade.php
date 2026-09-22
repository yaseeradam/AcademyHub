@props(['size' => 'md', 'text' => null])

@php
    $sizeClasses = [
        'sm' => 'h-4 w-4',
        'md' => 'h-8 w-8',
        'lg' => 'h-12 w-12',
        'xl' => 'h-16 w-16',
    ];
    $spinnerSize = $sizeClasses[$size] ?? $sizeClasses['md'];
@endphp

<div class="flex flex-col items-center justify-center gap-3">
    <div class="relative {{ $spinnerSize }}">
        <div class="absolute inset-0 rounded-full border-4 border-amber-200"></div>
        <div class="absolute inset-0 rounded-full border-4 border-amber-500 border-t-transparent animate-spin"></div>
    </div>
    @if($text)
        <p class="text-sm font-medium text-slate-600 animate-pulse">{{ $text }}</p>
    @endif
</div>
