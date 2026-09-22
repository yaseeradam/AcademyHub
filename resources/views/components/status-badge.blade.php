@props(['variant' => 'neutral'])

@php
    $classes = match ($variant) {
        'success' => 'bg-green-100 text-green-600',
        'warning' => 'bg-orange-100 text-orange-600',
        'info' => 'bg-brand-100 text-brand-700',
        'purple' => 'bg-purple-100 text-purple-600',
        default => 'bg-gray-100 text-gray-600',
    };
@endphp

<span {{ $attributes->class("inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {$classes}") }}>
    {{ $slot }}
</span>
