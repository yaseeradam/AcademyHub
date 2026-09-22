<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Timetable – {{ $class->name }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm 10mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            color: #1e293b;
            background: #ffffff;
        }

        /* ── Header layout ── */
        .header-table {
            width: 100%;
            border: none;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .header-table td {
            border: none;
            vertical-align: middle;
            padding: 0;
        }

        .logo-cell {
            width: 60px;
            padding-right: 12px;
        }

        .logo-cell img {
            width: 55px;
            height: 55px;
            border-radius: 6px;
        }

        .school-name {
            font-size: 20px;
            font-weight: bold;
            color: #0c4a6e;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }

        .school-details {
            font-size: 8px;
            color: #64748b;
        }

        /* ── Title bar ── */
        .title-bar {
            width: 100%;
            border: none;
            border-collapse: collapse;
            border-radius: 6px;
            margin-bottom: 12px;
        }

        .title-bar td {
            background: #0ea5e9;
            color: white;
            padding: 8px 16px;
            border: none;
            vertical-align: middle;
        }

        .title-bar td:first-child {
            border-radius: 6px 0 0 6px;
        }

        .title-bar td:last-child {
            border-radius: 0 6px 6px 0;
            text-align: right;
        }

        .doc-title {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .doc-subtitle {
            font-size: 9px;
            opacity: 0.9;
            margin-top: 2px;
        }

        .badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.25);
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
        }

        /* ── Timetable grid ── */
        .timetable {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #cbd5e1;
        }

        .timetable thead tr {
            background: #1e40af;
        }

        .timetable th {
            color: #ffffff;
            padding: 8px 4px;
            text-align: center;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #3b82f6;
        }

        .timetable th.time-col {
            width: 11%;
            background: #1e3a8a;
        }

        .timetable tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .timetable tbody tr:nth-child(odd) {
            background: #ffffff;
        }

        .timetable td {
            padding: 4px;
            border: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .timetable td.time-cell {
            background: #f1f5f9;
            font-weight: bold;
            text-align: center;
            color: #334155;
            font-size: 8px;
            vertical-align: middle;
            border-right: 2px solid #cbd5e1;
        }

        /* ── Entry card colors ── */
        .entry-card {
            border-radius: 4px;
            padding: 4px 5px;
            border: 1px solid #cbd5e1;
            border-left: 3px solid #64748b;
            background: #f1f5f9;
        }

        .entry-card.color-slate { background: #f1f5f9; border-color: #cbd5e1; border-left-color: #64748b; }
        .entry-card.color-blue { background: #eff6ff; border-color: #bfdbfe; border-left-color: #3b82f6; }
        .entry-card.color-indigo { background: #eef2ff; border-color: #c7d2fe; border-left-color: #4f46e5; }
        .entry-card.color-violet { background: #f5f3ff; border-color: #ddd6fe; border-left-color: #7c3aed; }
        .entry-card.color-purple { background: #faf5ff; border-color: #e9d5ff; border-left-color: #a855f7; }
        .entry-card.color-pink { background: #fdf2f8; border-color: #fbcfe8; border-left-color: #ec4899; }
        .entry-card.color-red { background: #fef2f2; border-color: #fca5a5; border-left-color: #ef4444; }
        .entry-card.color-orange { background: #fff7ed; border-color: #ffedd5; border-left-color: #f97316; }
        .entry-card.color-amber { background: #fffbeb; border-color: #fde68a; border-left-color: #f59e0b; }
        .entry-card.color-yellow { background: #fefce8; border-color: #fef08a; border-left-color: #eab308; }
        .entry-card.color-green { background: #f0fdf4; border-color: #bbf7d0; border-left-color: #22c55e; }
        .entry-card.color-emerald { background: #ecfdf5; border-color: #a7f3d0; border-left-color: #10b981; }
        .entry-card.color-teal { background: #f0fdfa; border-color: #99f6e4; border-left-color: #14b8a6; }
        .entry-card.color-cyan { background: #ecfeff; border-color: #a5f3fc; border-left-color: #06b6d4; }
        .entry-card.color-sky { background: #f0f9ff; border-color: #bae6fd; border-left-color: #0ea5e9; }

        .entry-card.is-break {
            padding: 8px 4px;
            text-align: center;
        }

        .break-title {
            font-size: 8px;
            font-weight: bold;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .entry-subject {
            font-size: 8.5px;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 2px;
        }

        .entry-teacher {
            font-size: 7.5px;
            color: #475569;
            margin-bottom: 1px;
        }

        .entry-room {
            font-size: 7px;
            color: #64748b;
            font-weight: 600;
        }

        .empty-cell {
            text-align: center;
            color: #cbd5e1;
            font-size: 11px;
            padding: 8px 0;
        }

        /* ── Footer ── */
        .footer-table {
            width: 100%;
            border: none;
            border-collapse: collapse;
            margin-top: 10px;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
        }

        .footer-table td {
            border: none;
            font-size: 7px;
            color: #94a3b8;
            padding: 4px 0 0 0;
            vertical-align: middle;
        }
    </style>
</head>

<body>

    {{-- ═══════ HEADER ═══════ --}}
    <table class="header-table" style="width: 100%; border-bottom: 2px solid #0c4a6e; padding-bottom: 8px; margin-bottom: 15px;">
        <tr>
            @if($logoBase64)
                <td class="logo-cell" style="width: 65px; vertical-align: middle;">
                    <img src="{{ $logoBase64 }}" alt="Logo" style="width: 60px; height: 60px; border-radius: 8px;">
                </td>
            @endif
            <td style="vertical-align: middle; text-align: left;">
                <div class="school-name" style="font-size: 18px; font-weight: 900; color: #0c4a6e; letter-spacing: 0.5px; text-transform: uppercase;">{{ $schoolName }}</div>
                @if($schoolAddress || $schoolPhone || $schoolEmail)
                    <div class="school-details" style="font-size: 8px; color: #475569; margin-top: 3px; font-weight: 500;">
                        {{ implode('  •  ', array_filter([$schoolAddress, $schoolPhone, $schoolEmail])) }}
                    </div>
                @endif
            </td>
            <td style="vertical-align: middle; text-align: right;">
                <div style="font-size: 14px; font-weight: 900; color: #0c4a6e; text-transform: uppercase; letter-spacing: 1px;">Weekly Timetable</div>
                <div style="font-size: 11px; font-weight: 800; color: #0ea5e9; margin-top: 2px;">
                    Class: {{ $class->name }}@if($section) — {{ $section->name }}@endif
                </div>
                <div style="font-size: 9px; font-weight: bold; color: #64748b; margin-top: 2px;">
                    {{ $termLabel }} &bull; {{ $sessionLabel }} Session
                </div>
            </td>
        </tr>
    </table>

    {{-- ═══════ TIMETABLE GRID ═══════ --}}
    <table class="timetable">
        <thead>
            <tr>
                <th class="time-col" style="background: #1e3a8a; width: 10%;">Time / Date</th>
                @foreach($timeSlots as $slot)
                    <th style="font-size: 8px;">{{ $slot['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php
                $rendered = [];
            @endphp
            @foreach($days as $dayNum => $dayName)
            <tr>
                <td class="time-cell" style="font-weight: bold; background: #f8fafc; border-right: 2px solid #cbd5e1; text-align: center; vertical-align: middle;">
                    {{ substr($dayName, 0, 3) }}
                </td>
                @foreach($timeSlots as $slot)
                    @php
                        // Check if already rendered as part of a rowspan
                        if (isset($rendered[$dayNum][$slot['key']])) {
                            continue;
                        }

                        $entry = $slotMap[$dayNum][$slot['key']] ?? null;
                    @endphp

                    @if($entry && $entry->is_break)
                        @php
                            $targetText = trim($entry->break_text ?? 'BREAK');
                            $rowspan = 1;
                            
                            for ($d = $dayNum + 1; $d <= 5; $d++) {
                                $nextEntry = $slotMap[$d][$slot['key']] ?? null;
                                if ($nextEntry && $nextEntry->is_break && strcasecmp(trim($nextEntry->break_text ?? 'BREAK'), $targetText) === 0) {
                                    $rowspan++;
                                } else {
                                    break;
                                }
                            }

                            // Mark subsequent days as rendered
                            for ($offset = 1; $offset < $rowspan; $offset++) {
                                $rendered[$dayNum + $offset][$slot['key']] = true;
                            }

                            $c = $entry->color ?? 'slate';
                            $bgColor = match($c) {
                                'blue'     => '#eff6ff',
                                'indigo'   => '#eef2ff',
                                'violet'   => '#f5f3ff',
                                'purple'   => '#faf5ff',
                                'pink'     => '#fdf2f8',
                                'red'      => '#fef2f2',
                                'orange'   => '#fff7ed',
                                'amber'    => '#fffbeb',
                                'yellow'   => '#fefce8',
                                'green'    => '#f0fdf4',
                                'emerald'  => '#ecfdf5',
                                'teal'     => '#f0fdfa',
                                'cyan'     => '#ecfeff',
                                'sky'      => '#f0f9ff',
                                default    => '#f1f5f9',
                            };
                            $textColor = match($c) {
                                'blue'     => '#1e3a8a',
                                'indigo'   => '#312e81',
                                'violet'   => '#4c1d95',
                                'purple'   => '#581c87',
                                'pink'     => '#9d174d',
                                'red'      => '#991b1b',
                                'orange'   => '#9a3412',
                                'amber'    => '#92400e',
                                'yellow'   => '#854d0e',
                                'green'    => '#166534',
                                'emerald'  => '#065f46',
                                'teal'     => '#115e59',
                                'cyan'     => '#155e75',
                                'sky'      => '#075985',
                                default    => '#334155',
                            };
                            $borderColor = match($c) {
                                'blue'     => '#bfdbfe',
                                'indigo'   => '#c7d2fe',
                                'violet'   => '#ddd6fe',
                                'purple'   => '#e9d5ff',
                                'pink'     => '#fbcfe8',
                                'red'      => '#fca5a5',
                                'orange'   => '#ffedd5',
                                'amber'    => '#fde68a',
                                'yellow'   => '#fef08a',
                                'green'    => '#bbf7d0',
                                'emerald'  => '#a7f3d0',
                                'teal'     => '#99f6e4',
                                'cyan'     => '#a5f3fc',
                                'sky'      => '#bae6fd',
                                default    => '#cbd5e1',
                            };
                        @endphp
                        <td rowspan="{{ $rowspan }}" class="is-break" style="vertical-align: middle; text-align: center; background-color: {{ $bgColor }}; color: {{ $textColor }}; border: 1px solid {{ $borderColor }}; padding: 6px 4px;">
                            @if($rowspan > 1)
                                <div style="display: inline-block; font-size: 8px; font-weight: 950; color: {{ $textColor }}; text-transform: uppercase; letter-spacing: 1px;">
                                    @php
                                        $chars = mb_str_split($targetText);
                                        foreach ($chars as $char) {
                                            if ($char === ' ') {
                                                echo '<span style="margin: 3px 0; display: block;"></span>';
                                            } else {
                                                echo '<span style="display: block; line-height: 1.1;">' . e($char) . '</span>';
                                            }
                                        }
                                    @endphp
                                </div>
                            @else
                                <div style="font-size: 8px; font-weight: 950; color: {{ $textColor }}; text-transform: uppercase; letter-spacing: 0.5px;">
                                    {{ $targetText }}
                                </div>
                            @endif
                        </td>
                    @else
                        <td>
                            @if($entry)
                                <div class="entry-card color-{{ $entry->color ?? 'slate' }}">
                                    <div class="entry-subject">{{ $entry->subject?->name ?? 'N/A' }}</div>
                                    <div class="entry-teacher">{{ $entry->teacher?->name ?? 'No Teacher' }}</div>
                                    @if($entry->room)
                                        <div class="entry-room">Room: {{ $entry->room }}</div>
                                    @endif
                                </div>
                            @else
                                <div class="empty-cell">—</div>
                            @endif
                        </td>
                    @endif
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ═══════ FOOTER ═══════ --}}
    <table class="footer-table">
        <tr>
            <td>{{ $schoolName }} &bull; {{ $termLabel }} &bull; {{ $sessionLabel }} Session</td>
            <td style="text-align: right;">Generated on {{ now()->format('F j, Y \a\t g:i A') }}</td>
        </tr>
    </table>

</body>

</html>