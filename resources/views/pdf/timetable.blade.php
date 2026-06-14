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
    <table class="header-table">
        <tr>
            @if($logoBase64)
                <td class="logo-cell">
                    <img src="{{ $logoBase64 }}" alt="Logo">
                </td>
            @endif
            <td>
                <div class="school-name">{{ $schoolName }}</div>
                @if($schoolAddress || $schoolPhone || $schoolEmail)
                    <div class="school-details">
                        {{ implode('  •  ', array_filter([$schoolAddress, $schoolPhone, $schoolEmail])) }}
                    </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- ═══════ TITLE BAR ═══════ --}}
    <table class="title-bar">
        <tr>
            <td>
                <div class="doc-title">Weekly Timetable</div>
                <div class="doc-subtitle">
                    {{ $class->name }}@if($section) — {{ $section->name }}@endif
                </div>
            </td>
            <td style="text-align: right;">
                <span class="badge">{{ $termLabel }}</span>
                <span class="badge" style="margin-left: 4px;">{{ $sessionLabel }}</span>
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
            @foreach($days as $dayNum => $dayName)
            <tr>
                <td class="time-cell" style="font-weight: bold; background: #f8fafc; border-right: 2px solid #cbd5e1; text-align: center; vertical-align: middle;">
                    {{ substr($dayName, 0, 3) }}
                </td>
                @foreach($timeSlots as $slot)
                <td>
                    @if(isset($slotMap[$dayNum][$slot['key']]))
                    @php($entry = $slotMap[$dayNum][$slot['key']])
                        <div class="entry-card color-{{ $entry->color ?? 'slate' }} {{ $entry->is_break ? 'is-break' : '' }}">
                            @if($entry->is_break)
                                <div class="break-title">{{ $entry->break_text ?? 'BREAK' }}</div>
                            @else
                                <div class="entry-subject">{{ $entry->subject?->name ?? 'N/A' }}</div>
                                <div class="entry-teacher">{{ $entry->teacher?->name ?? 'No Teacher' }}</div>
                                @if($entry->room)
                                    <div class="entry-room">Room: {{ $entry->room }}</div>
                                @endif
                            @endif
                        </div>
                    @else
                    <div class="empty-cell">—</div>
                    @endif
                </td>
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