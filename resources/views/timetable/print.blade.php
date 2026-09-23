<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timetable Poster – {{ $class->name }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@400;500;600;700;800&family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Fredoka', 'Nunito', ui-rounded, system-ui, -apple-system, sans-serif;
            background: #e2e8f0;
            color: #163c55;
            margin: 0;
            padding: 24px;
        }

        .font-display {
            font-family: 'Fredoka', 'Nunito', ui-rounded, system-ui, -apple-system, sans-serif !important;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            #timetable-poster-wrapper {
                box-shadow: none !important;
                border: none !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                page-break-inside: avoid;
            }
            @page {
                size: A4 landscape;
                margin: 6mm 4mm;
            }
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-start antialiased">

    {{-- Floating Print Control Bar --}}
    <div class="no-print fixed top-4 right-6 z-50 flex items-center gap-3 bg-white/95 backdrop-blur-md px-5 py-2.5 rounded-full shadow-lg border border-slate-200">
        <span class="text-xs font-bold text-slate-600 hidden sm:inline">
            📄 Recommendation: Landscape A4
        </span>
        <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-full bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs px-4 py-2 shadow transition-all hover:scale-105">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print Poster
        </button>
        <button onclick="window.close()" class="inline-flex items-center gap-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs px-3 py-2 transition-colors">
            Close
        </button>
    </div>

    {{-- Main Poster Sheet --}}
    <div class="w-full max-w-[1280px] my-auto">
        <div id="timetable-poster-wrapper" class="w-full bg-[#f8fbff] rounded-3xl shadow-2xl border-2 border-sky-100 overflow-hidden font-display flex flex-col">
            
            {{-- Header --}}
            @include('partials.timetable.header', [
                'schoolName' => $schoolName,
                'schoolTagline' => $schoolTagline,
                'className' => $class->name . ($section ? ' (' . $section->name . ')' : ''),
                'logoBase64' => $logoBase64,
                'logoUrl' => null,
            ])

            {{-- Grid --}}
            <div class="w-full overflow-x-auto p-4 sm:p-6">
                <table class="w-full border-separate border-spacing-2.5">
                    <thead>
                        <tr>
                            <th class="p-1.5 text-center" style="width: 140px; min-width: 140px;">
                                <div class="rounded-2xl bg-[#163c55] text-white py-3 px-3 flex items-center justify-center gap-2 shadow-sm border border-[#0ea5e9]/30">
                                    <span class="text-base sm:text-lg">📅</span>
                                    <span class="text-xs sm:text-sm font-black uppercase tracking-wider">Days</span>
                                </div>
                            </th>
                            @foreach($timeSlots as $slot)
                                <th class="p-1.5 text-center">
                                    <div class="rounded-2xl bg-[#163c55] text-white py-3 px-3 flex items-center justify-center gap-1.5 shadow-sm border border-[#0ea5e9]/30">
                                        <span class="text-sm opacity-90">🕒</span>
                                        <span class="text-xs sm:text-sm font-black whitespace-nowrap tracking-wide">{{ $slot['start'] }}-{{ $slot['end'] }}</span>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $rendered = [];
                        @endphp
                        @foreach($days as $dayNum => $dayName)
                            @php
                                $dayPillStyle = match($dayNum) {
                                    1 => 'bg-[#0284c7] text-white shadow-sky-600/30',
                                    2 => 'bg-[#16a34a] text-white shadow-emerald-600/30',
                                    3 => 'bg-[#f59e0b] text-white shadow-amber-600/30',
                                    4 => 'bg-[#7c3aed] text-white shadow-purple-600/30',
                                    5 => 'bg-[#e11d48] text-white shadow-rose-600/30',
                                    6 => 'bg-[#0d9488] text-white shadow-teal-600/30',
                                    default => 'bg-[#163c55] text-white',
                                };
                                $rowPastelTheme = match($dayNum) {
                                    1 => ['bg' => 'bg-[#e0f2fe]', 'text' => 'text-[#034f75]', 'border' => 'border-[#bae6fd]'],
                                    2 => ['bg' => 'bg-[#dcfce7]', 'text' => 'text-[#14532d]', 'border' => 'border-[#bbf7d0]'],
                                    3 => ['bg' => 'bg-[#fef3c7]', 'text' => 'text-[#78350f]', 'border' => 'border-[#fde68a]'],
                                    4 => ['bg' => 'bg-[#f3e8ff]', 'text' => 'text-[#4c1d95]', 'border' => 'border-[#ddd6fe]'],
                                    5 => ['bg' => 'bg-[#ffe4e6]', 'text' => 'text-[#881337]', 'border' => 'border-[#fecdd3]'],
                                    6 => ['bg' => 'bg-[#ccfbf1]', 'text' => 'text-[#115e59]', 'border' => 'border-[#99f6e4]'],
                                    default => ['bg' => 'bg-slate-100', 'text' => 'text-slate-900', 'border' => 'border-slate-200'],
                                };
                            @endphp
                            <tr>
                                <td class="p-1.5 align-middle text-center" style="width: 140px; min-width: 140px;">
                                    <div class="rounded-2xl py-3.5 px-3 sm:px-4 font-black text-xs sm:text-sm tracking-wide shadow-md flex items-center justify-center {{ $dayPillStyle }}">
                                        {{ $dayName }}
                                    </div>
                                </td>

                                @foreach($timeSlots as $slot)
                                    @php
                                        if (isset($rendered[$dayNum][$slot['key']])) {
                                            continue;
                                        }
                                        $entry = $slotMap[$dayNum][$slot['key']] ?? null;
                                    @endphp

                                    @if($entry && $entry->is_break)
                                        @php
                                            $targetText = trim($entry->break_text ?? 'BREAK');
                                            $rowspan = 1;
                                            $dayKeys = array_keys($days);
                                            $currentIdx = array_search($dayNum, $dayKeys);
                                            for ($k = $currentIdx + 1; $k < count($dayKeys); $k++) {
                                                $nextDayNum = $dayKeys[$k];
                                                $nextEntry = $slotMap[$nextDayNum][$slot['key']] ?? null;
                                                if ($nextEntry && $nextEntry->is_break && strcasecmp(trim($nextEntry->break_text ?? 'BREAK'), $targetText) === 0) {
                                                    $rowspan++;
                                                } else {
                                                    break;
                                                }
                                            }
                                            for ($offset = 1; $offset < $rowspan; $offset++) {
                                                $rendered[$dayKeys[$currentIdx + $offset]][$slot['key']] = true;
                                            }
                                        @endphp
                                        <td rowspan="{{ $rowspan }}" class="p-1.5 align-middle text-center">
                                            <div class="h-full min-h-[95px] w-full rounded-2xl bg-gradient-to-b from-[#fffbeb] via-[#fef08a]/80 to-[#fef08a] border-2 border-amber-300 p-3 flex flex-col items-center justify-center gap-1.5 shadow-sm text-amber-950 relative overflow-hidden">
                                                <span class="absolute top-2 left-2 text-amber-400 text-xs">✦</span>
                                                <span class="absolute bottom-2 right-2 text-amber-400 text-xs">★</span>
                                                
                                                <svg class="h-8 w-8 sm:h-9 sm:w-9 text-amber-800" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M18 8h1a4 4 0 0 1 0 8h-1"/>
                                                    <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/>
                                                    <line x1="6" y1="1" x2="6" y2="4"/>
                                                    <line x1="10" y1="1" x2="10" y2="4"/>
                                                    <line x1="14" y1="1" x2="14" y2="4"/>
                                                </svg>

                                                <span class="font-black text-sm sm:text-base text-amber-950 tracking-wider uppercase leading-tight font-display">
                                                    {{ $targetText }}
                                                </span>
                                            </div>
                                        </td>
                                    @else
                                        <td class="p-1.5 align-middle">
                                            @if($entry)
                                                @php
                                                    $c = $entry->color;
                                                    $hasCustomColor = ($c && $c !== 'slate');
                                                    $cardBg = $hasCustomColor ? match($c) {
                                                        'blue'    => 'bg-blue-50 text-blue-950 border-blue-200',
                                                        'indigo'  => 'bg-indigo-50 text-indigo-950 border-indigo-200',
                                                        'violet'  => 'bg-violet-50 text-violet-950 border-violet-200',
                                                        'purple'  => 'bg-purple-50 text-purple-950 border-purple-200',
                                                        'pink'    => 'bg-pink-50 text-pink-950 border-pink-200',
                                                        'red'     => 'bg-red-50 text-red-950 border-red-200',
                                                        'orange'  => 'bg-orange-50 text-orange-950 border-orange-200',
                                                        'amber'   => 'bg-amber-50 text-amber-950 border-amber-200',
                                                        'yellow'  => 'bg-yellow-50 text-yellow-950 border-yellow-200',
                                                        'green'   => 'bg-green-50 text-green-950 border-green-200',
                                                        'emerald' => 'bg-emerald-50 text-emerald-950 border-emerald-200',
                                                        'teal'    => 'bg-teal-50 text-teal-950 border-teal-200',
                                                        'cyan'    => 'bg-cyan-50 text-cyan-950 border-cyan-200',
                                                        'sky'     => 'bg-sky-50 text-sky-950 border-sky-200',
                                                        default   => "{$rowPastelTheme['bg']} {$rowPastelTheme['text']} {$rowPastelTheme['border']}",
                                                    } : "{$rowPastelTheme['bg']} {$rowPastelTheme['text']} {$rowPastelTheme['border']}";
                                                @endphp
                                                <div class="w-full h-full min-h-[58px] rounded-2xl border {{ $cardBg }} px-3 py-2.5 text-center flex flex-col items-center justify-center shadow-xs">
                                                    <div class="text-xs sm:text-sm font-black truncate max-w-full leading-tight font-display">
                                                        {{ $entry->subject?->name ?? 'Subject' }}
                                                    </div>
                                                    @if($entry->teacher?->name)
                                                        <div class="mt-1 text-[10px] font-bold opacity-75 truncate max-w-full">
                                                            {{ $entry->teacher->name }}
                                                        </div>
                                                    @endif
                                                    @if($entry->room)
                                                        <div class="mt-0.5 text-[9px] font-bold opacity-65 truncate max-w-full">
                                                            📍 {{ $entry->room }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="w-full h-full min-h-[58px] flex items-center justify-center text-slate-300 font-black text-sm">
                                                    —
                                                </div>
                                            @endif
                                        </td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Footer --}}
            @include('partials.timetable.footer')

        </div>
    </div>

    @if(request()->has('autoprint'))
        <script>
            window.addEventListener('load', () => {
                setTimeout(() => window.print(), 600);
            });
        </script>
    @endif

</body>
</html>
