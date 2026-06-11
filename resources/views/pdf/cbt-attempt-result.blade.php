<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Aptitude Test Result - {{ $candidateName }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; line-height: 1.5; color: #1e293b; padding: 30px; background-color: #ffffff; }
        
        .school-header { text-align: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e2e8f0; position: relative; }
        .logo-container { margin-bottom: 8px; }
        .school-logo { width: 70px; height: 70px; object-fit: contain; }
        .school-name { font-size: 18pt; font-weight: bold; color: #0f766e; margin-bottom: 4px; }
        .school-address { font-size: 8.5pt; color: #64748b; margin-bottom: 2px; }
        .school-contact { font-size: 8.5pt; color: #64748b; }
        
        .result-title { text-align: center; background: #0f766e; color: white; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
        .result-title h1 { font-size: 15pt; text-transform: uppercase; letter-spacing: 1px; }
        
        .grid-container { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .grid-container td { padding: 8px 12px; border: 1px solid #e2e8f0; vertical-align: top; }
        .info-label { background: #f8fafc; font-weight: bold; color: #0f766e; width: 25%; }
        .info-value { color: #334155; }
        
        .score-summary-box { 
            background: #f0fdfa; 
            border: 1.5px solid #ccfbf1; 
            border-radius: 10px; 
            padding: 15px; 
            margin-bottom: 25px; 
            text-align: center; 
        }
        .score-summary-title { font-size: 10pt; font-weight: bold; color: #0f766e; text-transform: uppercase; margin-bottom: 5px; }
        .score-badge { display: inline-block; font-size: 24pt; font-weight: bold; color: #0d9488; margin-bottom: 4px; }
        .score-percentage { font-size: 14pt; font-weight: bold; color: #0f766e; }
        .status-badge { 
            display: inline-block; 
            margin-top: 8px; 
            padding: 4px 16px; 
            font-size: 9.5pt; 
            font-weight: bold; 
            border-radius: 20px; 
            text-transform: uppercase; 
        }
        .status-passed { background: #d1fae5; color: #065f46; }
        .status-failed { background: #fee2e2; color: #991b1b; }
        
        .section-header { 
            font-size: 11pt; 
            font-weight: bold; 
            color: #0f766e; 
            margin-bottom: 12px; 
            border-bottom: 1.5px solid #0f766e; 
            padding-bottom: 4px; 
            text-transform: uppercase; 
        }
        
        .question-block { 
            margin-bottom: 18px; 
            padding: 12px; 
            background: #fff; 
            border: 1px solid #e2e8f0; 
            border-radius: 6px; 
            page-break-inside: avoid; 
        }
        .question-meta { margin-bottom: 6px; font-size: 9pt; }
        .question-number { font-weight: bold; color: #0f766e; }
        .question-marks { float: right; background: #e2e8f0; color: #475569; padding: 1px 6px; border-radius: 4px; font-weight: bold; }
        .question-prompt { font-size: 10pt; font-weight: bold; color: #1e293b; margin-bottom: 8px; }
        
        .options-list { margin-left: 10px; margin-bottom: 8px; }
        .option-item { font-size: 9.5pt; padding: 4px 8px; margin-bottom: 3px; border-radius: 4px; background: #f8fafc; }
        .option-correct { background: #d1fae5; color: #065f46; font-weight: bold; }
        .option-incorrect { background: #fee2e2; color: #991b1b; }
        .option-selected { border: 1px solid #0f766e; }
        
        .answer-status { 
            margin-top: 6px; 
            padding: 4px 8px; 
            font-size: 9pt; 
            border-radius: 4px; 
            font-weight: bold; 
        }
        .status-correct-bg { background: #ecfdf5; color: #047857; border-left: 3px solid #10b981; }
        .status-incorrect-bg { background: #fef2f2; color: #b91c1c; border-left: 3px solid #ef4444; }
        .status-unanswered-bg { background: #f1f5f9; color: #475569; border-left: 3px solid #94a3b8; }
        
        .footer { margin-top: 35px; text-align: center; font-size: 8pt; color: #64748b; border-top: 1px solid #e2e8f0; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="school-header">
        @if(isset($logoBase64) && $logoBase64)
            <div class="logo-container">
                <img src="{{ $logoBase64 }}" alt="School Logo" class="school-logo">
            </div>
        @endif
        <div class="school-name">{{ $schoolName }}</div>
        @if($schoolAddress)
            <div class="school-address">{{ $schoolAddress }}</div>
        @endif
        @if($schoolPhone || $schoolEmail)
            <div class="school-contact">
                @if($schoolPhone) Tel: {{ $schoolPhone }} @endif
                @if($schoolPhone && $schoolEmail) | @endif
                @if($schoolEmail) Email: {{ $schoolEmail }} @endif
            </div>
        @endif
    </div>

    <div class="result-title">
        <h1>Aptitude Test Result Card</h1>
    </div>

    <table class="grid-container">
        <tr>
            <td class="info-label">Candidate Name</td>
            <td class="info-value">{{ $candidateName }}</td>
            <td class="info-label">Candidate ID</td>
            <td class="info-value font-mono">{{ $attempt->student->admission_number ?? ('APT-' . strtoupper(substr(md5($candidateName), 0, 6))) }}</td>
        </tr>
        <tr>
            <td class="info-label">Test Name</td>
            <td class="info-value">{{ $attempt->exam->title }}</td>
            <td class="info-label">Class Category</td>
            <td class="info-value">{{ $attempt->student->schoolClass->name ?? 'Aptitude Candidate' }}</td>
        </tr>
        <tr>
            <td class="info-label">Date Completed</td>
            <td class="info-value">{{ $attempt->submitted_at?->format('F j, Y \a\t g:i A') ?? '-' }}</td>
            <td class="info-label">Time Spent</td>
            <td class="info-value">
                @php
                    $duration = 0;
                    if ($attempt->started_at && $attempt->submitted_at) {
                        $duration = $attempt->started_at->diffInSeconds($attempt->submitted_at);
                    }
                    $mins = floor($duration / 60);
                    $secs = $duration % 60;
                    echo "{$mins}m {$secs}s";
                @endphp
            </td>
        </tr>
    </table>

    <div class="score-summary-box">
        <div class="score-summary-title">Summary Performance</div>
        <div class="score-badge">{{ $attempt->score }} <span style="font-size:16pt; color:#64748b; font-weight:normal;">/ {{ $attempt->max_score }} marks</span></div>
        <div>
            <span class="score-percentage">{{ $attempt->percent }}%</span>
        </div>
        @php
            $passed = $attempt->percent >= 50;
        @endphp
        <div class="status-badge {{ $passed ? 'status-passed' : 'status-failed' }}">
            {{ $passed ? 'Passed' : 'Failed' }}
        </div>
    </div>

    <div class="section-header">Question & Answer Breakdown</div>

    @foreach ($attempt->exam->questions as $q)
        @php
            $ans = $attempt->answers->firstWhere('question_id', $q->id);
            $selectedOptId = $ans ? $ans->option_id : null;
            $correctOpt = $q->options->firstWhere('is_correct', true);
            $correctOptId = $correctOpt ? $correctOpt->id : null;
        @endphp
        <div class="question-block">
            <div class="question-meta">
                <span class="question-number">Question {{ $loop->iteration }}</span>
                <span class="question-marks">{{ $q->marks }} mark{{ $q->marks > 1 ? 's' : '' }}</span>
            </div>
            <div class="question-prompt">{{ $q->prompt }}</div>
            
            @if ($q->type === 'theory')
                <div style="margin-top: 5px; font-size: 9.5pt;">
                    <div style="color: #64748b; font-weight: bold; margin-bottom: 2px;">Your Answer:</div>
                    <div style="padding: 6px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; font-style: italic;">
                        {{ $ans && trim((string) $ans->text_answer) !== '' ? $ans->text_answer : 'No Answer Submitted' }}
                    </div>
                </div>
            @else
                <div class="options-list">
                    @foreach ($q->options as $opt)
                        @php
                            $isSel = $opt->id === $selectedOptId;
                            $isCorr = $opt->id === $correctOptId;
                            
                            $class = 'option-item';
                            if ($isSel && $isCorr) {
                                $class .= ' option-correct option-selected';
                            } elseif ($isSel && !$isCorr) {
                                $class .= ' option-incorrect option-selected';
                            } elseif ($isCorr) {
                                $class .= ' option-correct';
                            }
                        @endphp
                        <div class="{{ $class }}">
                            <span style="font-weight: bold;">{{ chr(65 + $loop->index) }}.</span>
                            {{ $opt->label }}
                            @if ($isSel) <span style="font-size: 8pt; font-style: italic; color: #475569;">(Your Choice)</span> @endif
                            @if ($isCorr) <span style="font-size: 8pt; font-style: italic; color: #047857;">(Correct Answer)</span> @endif
                        </div>
                    @endforeach
                </div>
                
                @if ($ans && $ans->is_correct)
                    <div class="answer-status status-correct-bg">
                        Correct (Score: {{ $q->marks }} marks awarded)
                    </div>
                @elseif ($ans && !$ans->is_correct && $ans->option_id)
                    <div class="answer-status status-incorrect-bg">
                        Incorrect (Score: 0 marks awarded)
                    </div>
                @else
                    <div class="answer-status status-unanswered-bg">
                        Unanswered / Incorrect (Score: 0 marks awarded)
                    </div>
                @endif
            @endif
        </div>
    @endforeach

    <div class="footer">
        <strong>{{ $schoolName }}</strong> &nbsp;&bull;&nbsp; Generated on {{ now()->format('F j, Y \a\t g:i A') }}
    </div>
</body>
</html>
