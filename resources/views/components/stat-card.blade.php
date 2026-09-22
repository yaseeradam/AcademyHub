@props([
    'label',
    'value',
    'cardBg' => 'bg-white',
    'ringColor' => 'ring-gray-100',
    'iconBg' => 'bg-gray-100',
    'iconColor' => 'text-gray-700',
])

@php
    $gradients = [
        'blue' => ['bg' => 'from-blue-50 to-blue-100/50', 'ring' => 'ring-blue-200/50', 'text' => 'text-blue-600', 'icon' => 'from-blue-400 to-blue-600 shadow-blue-500/30', 'accent' => 'bg-blue-500/5'],
        'purple' => ['bg' => 'from-purple-50 to-purple-100/50', 'ring' => 'ring-purple-200/50', 'text' => 'text-purple-600', 'icon' => 'from-purple-400 to-purple-600 shadow-purple-500/30', 'accent' => 'bg-purple-500/5'],
        'green' => ['bg' => 'from-green-50 to-green-100/50', 'ring' => 'ring-green-200/50', 'text' => 'text-green-600', 'icon' => 'from-green-400 to-green-600 shadow-green-500/30', 'accent' => 'bg-green-500/5'],
        'emerald' => ['bg' => 'from-emerald-50 to-emerald-100/50', 'ring' => 'ring-emerald-200/50', 'text' => 'text-emerald-600', 'icon' => 'from-emerald-400 to-emerald-600 shadow-emerald-500/30', 'accent' => 'bg-emerald-500/5'],
        'violet' => ['bg' => 'from-violet-50 to-violet-100/50', 'ring' => 'ring-violet-200/50', 'text' => 'text-violet-600', 'icon' => 'from-violet-400 to-violet-600 shadow-violet-500/30', 'accent' => 'bg-violet-500/5'],
        'amber' => ['bg' => 'from-amber-50 to-amber-100/50', 'ring' => 'ring-amber-200/50', 'text' => 'text-amber-600', 'icon' => 'from-amber-400 to-amber-600 shadow-amber-500/30', 'accent' => 'bg-amber-500/5'],
        'cyan' => ['bg' => 'from-cyan-50 to-cyan-100/50', 'ring' => 'ring-cyan-200/50', 'text' => 'text-cyan-600', 'icon' => 'from-cyan-400 to-cyan-600 shadow-cyan-500/30', 'accent' => 'bg-cyan-500/5'],
        'pink' => ['bg' => 'from-pink-50 to-pink-100/50', 'ring' => 'ring-pink-200/50', 'text' => 'text-pink-600', 'icon' => 'from-pink-400 to-pink-600 shadow-pink-500/30', 'accent' => 'bg-pink-500/5'],
        'indigo' => ['bg' => 'from-indigo-50 to-indigo-100/50', 'ring' => 'ring-indigo-200/50', 'text' => 'text-indigo-600', 'icon' => 'from-indigo-400 to-indigo-600 shadow-indigo-500/30', 'accent' => 'bg-indigo-500/5'],
    ];
    
    $colorKey = explode('-', $iconColor)[1] ?? 'blue';
    $colors = $gradients[$colorKey] ?? $gradients['blue'];
@endphp

<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br {{ $colors['bg'] }} p-5 shadow-sm ring-1 {{ $colors['ring'] }} transition-all duration-300 hover:shadow-md hover:-translate-y-0.5">
    <div class="absolute -right-6 -top-6 h-20 w-20 rounded-full {{ $colors['accent'] }}"></div>
    <div class="flex items-center gap-4">
        @php
            $iconSlot = $icon ?? null;
            $iconContent = $iconSlot && ! $iconSlot->isEmpty() ? $iconSlot : $slot;
            $hasIcon = $iconContent && ! $iconContent->isEmpty();
        @endphp

        @if ($hasIcon)
            <div class="icon-3d grid h-14 w-14 place-items-center rounded-xl bg-gradient-to-br {{ $colors['icon'] }} text-white shadow-lg">
                {{ $iconContent }}
            </div>
        @endif
        <div class="min-w-0">
            <div class="text-xs font-medium uppercase tracking-wide {{ $colors['text'] }}">{{ $label }}</div>
            <div class="mt-1.5 text-2xl font-bold tracking-tight text-gray-900">{{ $value }}</div>
        </div>
    </div>
</div>
