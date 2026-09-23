<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Timetable – {{ $class->name }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 6mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 8px;
            color: #163c55;
            background: #ffffff;
        }

        /* ── Poster Wrapper ── */
        .poster-container {
            width: 100%;
            border: 2px solid #bae6fd;
            border-radius: 12px;
            background: #f8fbff;
            padding: 8px 10px;
        }

        /* ── Header Table ── */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #e0f2fe;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .header-table td {
            vertical-align: middle;
            border: none;
        }

        .logo-cell {
            width: 70px;
            text-align: center;
        }

        .logo-circle {
            width: 58px;
            height: 58px;
            border-radius: 29px;
            border: 2px solid #163c55;
        }

        .crest-fallback {
            width: 56px;
            height: 56px;
            border-radius: 28px;
            background: #163c55;
            color: #ffffff;
            text-align: center;
            padding-top: 10px;
            border: 2px solid #0ea5e9;
        }

        .school-title {
            font-size: 16px;
            font-weight: 900;
            color: #163c55;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.1;
        }

        .school-motto {
            font-size: 8.5px;
            color: #1e40af;
            font-style: italic;
            font-weight: bold;
            margin-top: 2px;
        }

        .class-badge-container {
            margin-top: 5px;
            display: inline-block;
        }

        .class-badge {
            display: inline-block;
            background: #ffffff;
            border: 2px solid #163c55;
            border-radius: 16px;
            padding: 3px 14px;
            font-size: 13px;
            font-weight: 900;
            letter-spacing: 1px;
            color: #0284c7;
            text-transform: uppercase;
        }

        .ribbon-banner {
            display: inline-block;
            background: #f59e0b;
            color: #163c55;
            font-weight: 900;
            font-size: 9px;
            letter-spacing: 2px;
            text-transform: uppercase;
            padding: 2px 16px;
            border: 1px solid #163c55;
            border-radius: 3px;
            margin-top: -3px;
        }

        .header-right {
            width: 140px;
            text-align: right;
            vertical-align: middle;
        }

        .quote-badge {
            font-size: 9.5px;
            font-weight: 900;
            color: #163c55;
            line-height: 1.2;
        }

        .sub-details {
            font-size: 7.5px;
            color: #64748b;
            font-weight: bold;
            margin-top: 4px;
        }

        /* ── Timetable Grid Table ── */
        .timetable {
            width: 100%;
            border-collapse: separate;
            border-spacing: 4px;
            margin-bottom: 6px;
        }

        .timetable th {
            background: #163c55;
            color: #ffffff;
            font-size: 8px;
            font-weight: bold;
            text-align: center;
            padding: 6px 3px;
            border-radius: 6px;
            border: 1px solid #0ea5e9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .timetable th.day-header {
            width: 75px;
            background: #0f2c42;
        }

        /* ── Day Badges ── */
        .day-cell {
            color: #ffffff;
            font-weight: 900;
            font-size: 8.5px;
            text-align: center;
            vertical-align: middle;
            border-radius: 6px;
            padding: 5px 2px;
            width: 75px;
        }

        .day-1 { background: #0284c7; } /* Monday */
        .day-2 { background: #16a34a; } /* Tuesday */
        .day-3 { background: #f59e0b; } /* Wednesday */
        .day-4 { background: #7c3aed; } /* Thursday */
        .day-5 { background: #e11d48; } /* Friday */
        .day-6 { background: #0d9488; } /* Saturday */

        /* ── Subject Cards ── */
        .subject-cell {
            vertical-align: middle;
            text-align: center;
            padding: 5px 3px;
            border-radius: 6px;
            height: 48px;
        }

        .subject-title {
            font-size: 8.5px;
            font-weight: 900;
            line-height: 1.1;
        }

        .subject-teacher {
            font-size: 6.5px;
            opacity: 0.8;
            margin-top: 1px;
            font-weight: 600;
        }

        .subject-room {
            font-size: 6px;
            opacity: 0.7;
            margin-top: 1px;
            font-weight: bold;
        }

        /* ── Row Pastel Colors ── */
        .row-pastel-1 { background: #e0f2fe; color: #034f75; border: 1px solid #bae6fd; }
        .row-pastel-2 { background: #dcfce7; color: #14532d; border: 1px solid #bbf7d0; }
        .row-pastel-3 { background: #fef3c7; color: #78350f; border: 1px solid #fde68a; }
        .row-pastel-4 { background: #f3e8ff; color: #4c1d95; border: 1px solid #ddd6fe; }
        .row-pastel-5 { background: #ffe4e6; color: #881337; border: 1px solid #fecdd3; }
        .row-pastel-6 { background: #ccfbf1; color: #115e59; border: 1px solid #99f6e4; }

        /* ── Breakfast / Break Cell ── */
        .break-cell {
            background: #fef08a;
            border: 1px solid #fde047;
            color: #78350f;
            vertical-align: middle;
            text-align: center;
            border-radius: 8px;
            padding: 6px 2px;
        }

        .break-title {
            font-size: 9px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #78350f;
        }

        .empty-cell {
            background: #ffffff;
            border: 1px dashed #cbd5e1;
            color: #cbd5e1;
            border-radius: 6px;
            text-align: center;
            vertical-align: middle;
            font-size: 9px;
            font-weight: bold;
            height: 48px;
        }

        /* ── Footer Table ── */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            border-top: 2px solid #e0f2fe;
            padding-top: 5px;
            margin-top: 4px;
        }

        .footer-table td {
            vertical-align: middle;
            border: none;
        }

        .values-banner {
            display: inline-block;
            background: #163c55;
            color: #ffffff;
            padding: 4px 20px;
            border-radius: 14px;
            font-size: 8px;
            font-weight: 900;
            letter-spacing: 0.5px;
            text-align: center;
        }
    </style>
</head>

<body>

<div class="poster-container">

    @php
        $schoolDisplayName = ($schoolName && $schoolName !== 'Your School Name' && $schoolName !== 'AcademyHub')
            ? $schoolName
            : 'ALIJABAH INTEGRATED ACADEMY ARGUNGU';

        $schoolTaglineText = ($schoolTagline && !str_contains($schoolTagline, "what's happening"))
            ? $schoolTagline
            : 'Learning Today | Leading Tomorrow';
    @endphp

    {{-- ═══════ HEADER ═══════ --}}
    <table class="header-table">
        <tr>
            {{-- Left: Logo / Crest --}}
            <td class="logo-cell">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" class="logo-circle" alt="Logo">
                @else
                    <div class="crest-fallback">
                        <div style="font-size: 8px; font-weight: 900; line-height: 1;">AIA</div>
                        <div style="font-size: 6px; margin-top: 2px;">ACADEMY</div>
                    </div>
                @endif
            </td>

            {{-- Center: School Name, Motto, Class Name Badge & TIMETABLE Ribbon --}}
            <td style="text-align: center;">
                <div class="school-title">{{ $schoolDisplayName }}</div>
                <div class="school-motto">— {{ $schoolTaglineText }} —</div>

                <div class="class-badge-container">
                    <div class="class-badge">
                        {{ strtoupper($class->name) }}@if($section) ({{ strtoupper($section->name) }})@endif
                    </div>
                    <br>
                    <div class="ribbon-banner">
                        TIMETABLE
                    </div>
                </div>
            </td>

            {{-- Right: Motivation & Meta info --}}
            <td class="header-right">
                <div class="quote-badge">
                    ☀️ Small Steps<br>
                    <strong>BIG DREAMS</strong>
                </div>
                <div class="sub-details">
                    {{ $termLabel }}<br>
                    {{ $sessionLabel }} Session
                </div>
            </td>
        </tr>
    </table>

    {{-- ═══════ TIMETABLE GRID (Days as Rows, Time Slots as Columns) ═══════ --}}
    <table class="timetable">
        <thead>
            <tr>
                <th class="day-header">Days</th>
                @foreach($timeSlots as $slot)
                    <th>{{ $slot['start'] }} – {{ $slot['end'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php
                $rendered = [];
            @endphp
            @foreach($days as $dayNum => $dayName)
            <tr>
                {{-- Day Label Pill --}}
                <td class="day-cell day-{{ $dayNum }}">
                    {{ $dayName }}
                </td>

                {{-- Subject / Break Cells --}}
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
                        <td rowspan="{{ $rowspan }}" class="break-cell">
                            <div style="font-size: 14px; margin-bottom: 3px;">☕</div>
                            <div class="break-title">{{ $targetText }}</div>
                        </td>
                    @else
                        @if($entry)
                            <td class="subject-cell row-pastel-{{ $dayNum }}">
                                <div class="subject-title">{{ $entry->subject?->name ?? 'Subject' }}</div>
                                @if($entry->teacher?->name)
                                    <div class="subject-teacher">{{ $entry->teacher->name }}</div>
                                @endif
                                @if($entry->room)
                                    <div class="subject-room">📍 {{ $entry->room }}</div>
                                @endif
                            </td>
                        @else
                            <td class="empty-cell">—</td>
                        @endif
                    @endif
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ═══════ FOOTER ═══════ --}}
    <table class="footer-table">
        <tr>
            <td style="width: 25%; font-size: 8px; font-weight: bold; color: #163c55;">
                📚 Learn &bull; Grow &bull; Succeed
            </td>
            <td style="text-align: center; width: 50%;">
                <div class="values-banner">
                    💡 Knowledge &nbsp;—&nbsp; ⭐ Character &nbsp;—&nbsp; 👥 Excellence
                </div>
            </td>
            <td style="text-align: right; width: 25%; font-size: 7px; color: #64748b;">
                Generated: {{ now()->format('M j, Y') }}
            </td>
        </tr>
    </table>

</div>

</body>
</html>