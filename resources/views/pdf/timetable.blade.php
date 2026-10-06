<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Conventional Timetable – {{ $class->name }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm 10mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8.5px;
            color: #0C1E40;
            background: #ffffff;
        }

        /* ── Outer Gold & Inner Navy Frame ── */
        .poster-outer {
            border: 3.5px solid #C59B27;
            padding: 6px;
            background: #ffffff;
        }

        .poster-inner {
            border: 2px solid #0C1E40;
            padding: 10px 14px 8px 14px;
            background: #ffffff;
        }

        /* ── Header ── */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .header-table td {
            vertical-align: middle;
            border: none;
        }

        .logo-box {
            width: 70px;
            height: 70px;
            border: 2px solid #C59B27;
            border-radius: 50%;
            text-align: center;
            background: #0C1E40;
            color: #ffffff;
            padding-top: 10px;
            box-sizing: border-box;
        }

        .school-title {
            font-size: 16px;
            font-weight: 900;
            color: #0C1E40;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }

        .motto-line {
            text-align: center;
            font-size: 8.5px;
            color: #8C6D15;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .doc-title {
            text-align: center;
            font-size: 12px;
            font-weight: 900;
            color: #0C1E40;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            text-decoration: underline;
        }

        /* ── Metadata Box ── */
        .meta-card {
            border: 1.5px solid #0C1E40;
            border-radius: 6px;
            padding: 4px 6px;
            background: #fdfbf7;
            width: 170px;
            font-size: 8px;
        }

        .meta-row {
            border-bottom: 0.5px solid #e2d9c2;
            padding: 2px 0;
        }

        .meta-label {
            font-weight: bold;
            color: #0C1E40;
            display: inline-block;
            width: 75px;
        }

        .meta-value {
            font-weight: 800;
            color: #1e293b;
        }

        /* ── Main Timetable Table ── */
        .timetable {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #0C1E40;
            margin-top: 4px;
        }

        .timetable th, .timetable td {
            border: 1.5px solid #0C1E40;
            text-align: center;
            vertical-align: middle;
        }

        .th-days {
            background: #0C1E40;
            color: #F6C445;
            font-size: 9.5px;
            font-weight: 900;
            width: 12%;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 4px;
        }

        .th-periods-bar {
            background: #0C1E40;
            color: #ffffff;
            font-size: 10px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 2px;
            padding: 4px;
        }

        .th-slot {
            background: #17274E;
            color: #ffffff;
            padding: 3px 2px;
            font-size: 8px;
        }

        .th-slot-num {
            font-size: 9.5px;
            font-weight: 900;
            color: #F6C445;
        }

        .th-slot-time {
            font-size: 7px;
            color: #dbeafe;
            white-space: nowrap;
        }

        .th-break {
            background: #DCE7F5;
            color: #0C1E40;
            font-size: 7.5px;
            font-weight: 900;
            width: 8%;
            padding: 2px;
        }

        .day-cell {
            background: #F4F7FC;
            font-weight: 900;
            color: #0C1E40;
            font-size: 8.5px;
            text-transform: uppercase;
            padding: 6px 3px;
        }

        .entry-cell {
            padding: 4px 2px;
            height: 38px;
        }

        .subject-name {
            font-size: 8.5px;
            font-weight: 900;
            color: #0C1E40;
            text-transform: uppercase;
            line-height: 1.1;
        }

        .teacher-name {
            font-size: 7px;
            color: #475569;
            margin-top: 1px;
        }

        .break-vertical {
            background: #DCE7F5;
            color: #0C1E40;
            font-weight: 900;
            text-align: center;
            vertical-align: middle;
            font-size: 7.5px;
            padding: 6px 2px;
        }

        /* ── Notes ── */
        .notes-table {
            width: 100%;
            margin-top: 6px;
            border-top: 1px solid #cbd5e1;
            padding-top: 4px;
            font-size: 7.5px;
            color: #334155;
        }

        /* ── Signatures ── */
        .signatures-table {
            width: 100%;
            margin-top: 16px;
            text-align: center;
        }

        .sig-line {
            border-bottom: 1.5px solid #0C1E40;
            width: 75%;
            margin: 0 auto 4px auto;
            height: 12px;
        }

        .sig-title {
            font-size: 8px;
            font-weight: 900;
            color: #0C1E40;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* ── Bottom Motto & Flourish ── */
        .bottom-motto-table {
            width: 100%;
            margin-top: 14px;
            text-align: center;
        }

        .bottom-motto-text {
            font-size: 9px;
            font-weight: 900;
            color: #0C1E40;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .flourish-line {
            border-top: 2px solid #C59B27;
            height: 2px;
            display: inline-block;
            width: 90px;
            vertical-align: middle;
        }
    </style>
</head>

<body>

    <div class="poster-outer">
        <div class="poster-inner">

            {{-- 1. POSTER HEADER --}}
            <table class="header-table">
                <tr>
                    {{-- Left Emblem --}}
                    <td style="width: 80px;">
                        @if($logoBase64)
                            <img src="{{ $logoBase64 }}" style="width: 65px; height: 65px; border-radius: 50%; border: 2px solid #C59B27;">
                        @else
                            <div class="logo-box">
                                <div style="font-size: 14px; font-weight: bold; color: #F6C445;">★</div>
                                <div style="font-size: 6.5px; font-weight: 900; letter-spacing: 0.5px;">ACADEMY</div>
                            </div>
                        @endif
                    </td>

                    {{-- Center Titles --}}
                    <td>
                        <div class="school-title">{{ $schoolName }}</div>
                        <div class="motto-line">
                            &mdash; MOTTO: <em>{{ $schoolMotto }}</em> &mdash;
                        </div>
                        <div class="doc-title">CONVENTIONAL TIMETABLE</div>
                    </td>

                    {{-- Right Meta Card --}}
                    <td style="width: 180px; text-align: right;">
                        <table class="meta-card">
                            <tr class="meta-row">
                                <td class="meta-label">CLASS:</td>
                                <td class="meta-value" style="font-size: 9px; color: #0C1E40;">{{ $class->name }}@if($section) ({{ $section->name }})@endif</td>
                            </tr>
                            <tr class="meta-row">
                                <td class="meta-label">TERM:</td>
                                <td class="meta-value">{{ $termLabel }}</td>
                            </tr>
                            <tr class="meta-row">
                                <td class="meta-label">SESSION:</td>
                                <td class="meta-value">{{ $sessionLabel }}</td>
                            </tr>
                            <tr>
                                <td class="meta-label" style="font-size: 7px;">CLASS TEACHER:</td>
                                <td class="meta-value" style="font-size: 7.5px; color: #8C6D15;">{{ $classTeacherName ?? 'NOT ASSIGNED' }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            {{-- 2. TIMETABLE GRID TABLE --}}
            <table class="timetable">
                <thead>
                    <tr>
                        <th rowspan="2" class="th-days">DAYS</th>
                        <th colspan="7" class="th-periods-bar">PERIODS &amp; TIME</th>
                    </tr>
                    <tr>
                        <th class="th-slot" style="width: 13%;">
                            <div class="th-slot-num">1</div>
                            <div class="th-slot-time">8:00am &ndash; 8:30am</div>
                        </th>
                        <th class="th-slot" style="width: 13%;">
                            <div class="th-slot-num">2</div>
                            <div class="th-slot-time">8:30am &ndash; 9:00am</div>
                        </th>
                        <th class="th-slot" style="width: 13%;">
                            <div class="th-slot-num">3</div>
                            <div class="th-slot-time">9:00am &ndash; 9:30am</div>
                        </th>
                        <th class="th-break">
                            <div style="font-weight: 900; font-size: 8px;">BREAK</div>
                            <div style="font-size: 6.5px; font-weight: bold;">9:30&ndash;9:40</div>
                        </th>
                        <th class="th-slot" style="width: 13%;">
                            <div class="th-slot-num">4</div>
                            <div class="th-slot-time">9:40am &ndash; 10:10am</div>
                        </th>
                        <th class="th-slot" style="width: 13%;">
                            <div class="th-slot-num">5</div>
                            <div class="th-slot-time">10:10am &ndash; 10:40am</div>
                        </th>
                        <th class="th-slot" style="width: 15%;">
                            <div class="th-slot-num">6</div>
                            <div class="th-slot-time">10:40am &ndash; 1:10pm</div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $weekdays = [
                            1 => 'MONDAY',
                            2 => 'TUESDAY',
                            3 => 'WEDNESDAY',
                            4 => 'THURSDAY',
                            5 => 'FRIDAY',
                        ];
                    @endphp

                    @foreach($weekdays as $dayNumber => $dayName)
                        <tr>
                            {{-- Day Cell --}}
                            <td class="day-cell">{{ $dayName }}</td>

                            {{-- Period 1 --}}
                            @php $slot0 = $conventionalMap[$dayNumber][0] ?? null; @endphp
                            <td class="entry-cell">
                                @if($slot0 && !$slot0->is_break)
                                    <div class="subject-name">{{ $slot0->subject?->name ?? 'Untitled' }}</div>
                                    @if($slot0->teacher?->name)
                                        <div class="teacher-name">{{ $slot0->teacher->name }}</div>
                                    @endif
                                @else
                                    <span style="color: #cbd5e1;">&mdash;</span>
                                @endif
                            </td>

                            {{-- Period 2 --}}
                            @php $slot1 = $conventionalMap[$dayNumber][1] ?? null; @endphp
                            <td class="entry-cell">
                                @if($slot1 && !$slot1->is_break)
                                    <div class="subject-name">{{ $slot1->subject?->name ?? 'Untitled' }}</div>
                                    @if($slot1->teacher?->name)
                                        <div class="teacher-name">{{ $slot1->teacher->name }}</div>
                                    @endif
                                @else
                                    <span style="color: #cbd5e1;">&mdash;</span>
                                @endif
                            </td>

                            {{-- Period 3 --}}
                            @php $slot2 = $conventionalMap[$dayNumber][2] ?? null; @endphp
                            <td class="entry-cell">
                                @if($slot2 && !$slot2->is_break)
                                    <div class="subject-name">{{ $slot2->subject?->name ?? 'Untitled' }}</div>
                                    @if($slot2->teacher?->name)
                                        <div class="teacher-name">{{ $slot2->teacher->name }}</div>
                                    @endif
                                @else
                                    <span style="color: #cbd5e1;">&mdash;</span>
                                @endif
                            </td>

                            {{-- Break Column (rowspan=5 on Monday) --}}
                            @if($dayNumber === 1)
                                <td rowspan="5" class="break-vertical">
                                    <div style="font-weight: 900; font-size: 8px; text-transform: uppercase; letter-spacing: 1px;">
                                        B<br>R<br>E<br>A<br>K
                                    </div>
                                    <div style="font-size: 6.5px; font-weight: bold; margin-top: 8px;">
                                        9:30am<br>&ndash;<br>9:40am
                                    </div>
                                </td>
                            @endif

                            {{-- Period 4 --}}
                            @php $slot4 = $conventionalMap[$dayNumber][4] ?? null; @endphp
                            <td class="entry-cell">
                                @if($slot4 && !$slot4->is_break)
                                    <div class="subject-name">{{ $slot4->subject?->name ?? 'Untitled' }}</div>
                                    @if($slot4->teacher?->name)
                                        <div class="teacher-name">{{ $slot4->teacher->name }}</div>
                                    @endif
                                @else
                                    <span style="color: #cbd5e1;">&mdash;</span>
                                @endif
                            </td>

                            {{-- Period 5 --}}
                            @php $slot5 = $conventionalMap[$dayNumber][5] ?? null; @endphp
                            <td class="entry-cell">
                                @if($slot5 && !$slot5->is_break)
                                    <div class="subject-name">{{ $slot5->subject?->name ?? 'Untitled' }}</div>
                                    @if($slot5->teacher?->name)
                                        <div class="teacher-name">{{ $slot5->teacher->name }}</div>
                                    @endif
                                @else
                                    <span style="color: #cbd5e1;">&mdash;</span>
                                @endif
                            </td>

                            {{-- Period 6 --}}
                            @php $slot6 = $conventionalMap[$dayNumber][6] ?? null; @endphp
                            <td class="entry-cell">
                                @if($slot6 && !$slot6->is_break)
                                    <div class="subject-name">{{ $slot6->subject?->name ?? 'Untitled' }}</div>
                                    @if($slot6->teacher?->name)
                                        <div class="teacher-name">{{ $slot6->teacher->name }}</div>
                                    @endif
                                @else
                                    <span style="color: #cbd5e1;">&mdash;</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- 3. NOTES ROW --}}
            <table class="notes-table">
                <tr>
                    <td style="text-align: left;">
                        <strong>NOTE:</strong> Periods 1 to 5: 30 minutes each &bull; Period 6: 2 hours 30 minutes
                    </td>
                    <td style="text-align: right;">
                        Closing Time: <strong>1:10 PM</strong> &bull; Recess: <strong>9:30 AM &ndash; 9:40 AM</strong>
                    </td>
                </tr>
            </table>

            {{-- 4. SIGNATURE LINES --}}
            <table class="signatures-table">
                <tr>
                    <td style="width: 50%;">
                        <div class="sig-line"></div>
                        <div class="sig-title">CLASS TEACHER'S SIGNATURE</div>
                    </td>
                    <td style="width: 50%;">
                        <div class="sig-line"></div>
                        <div class="sig-title">PRINCIPAL'S SIGNATURE</div>
                    </td>
                </tr>
            </table>

            {{-- 5. BOTTOM MOTTO & FLOURISHES --}}
            <table class="bottom-motto-table">
                <tr>
                    <td style="width: 30%; text-align: right;">
                        <span class="flourish-line"></span>
                    </td>
                    <td style="width: 40%; text-align: center;">
                        <span class="bottom-motto-text">{{ $schoolMotto }}</span>
                    </td>
                    <td style="width: 30%; text-align: left;">
                        <span class="flourish-line"></span>
                    </td>
                </tr>
            </table>

        </div>
    </div>

</body>
</html>