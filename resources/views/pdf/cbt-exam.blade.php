<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $exam->title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11pt; line-height: 1.6; color: #1a1a1a; padding: 30px; }
        .school-header { text-align: center; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 3px double #667eea; }
        .school-logo { width: 80px; height: 80px; margin: 0 auto 10px; }
        .school-name { font-size: 20pt; font-weight: bold; color: #667eea; margin-bottom: 5px; }
        .school-address { font-size: 9pt; color: #718096; margin-bottom: 3px; }
        .exam-title { text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .exam-title h1 { font-size: 18pt; margin-bottom: 8px; }
        .exam-info { display: table; width: 100%; margin-bottom: 20px; border: 2px solid #e2e8f0; border-radius: 8px; overflow: hidden; }
        .info-row { display: table-row; }
        .info-label { display: table-cell; padding: 10px 15px; background: #f7fafc; font-weight: bold; width: 30%; border-bottom: 1px solid #e2e8f0; color: #667eea; }
        .info-value { display: table-cell; padding: 10px 15px; border-bottom: 1px solid #e2e8f0; }
        .instructions { background: #f8f9fa; padding: 15px; border-left: 4px solid #667eea; margin-bottom: 25px; font-size: 10pt; }
        .instructions strong { color: #667eea; }
        .question { margin-bottom: 25px; page-break-inside: avoid; background: white; padding: 15px; border: 1px solid #e0e0e0; border-radius: 8px; }
        .question-number { font-weight: bold; font-size: 12pt; margin-bottom: 10px; color: #667eea; }
        .question-text { margin-bottom: 12px; color: #2d3748; }
        .options { margin-left: 0; }
        .option { padding: 8px 12px; margin-bottom: 6px; background: #f7fafc; border: 1px solid #e2e8f0; border-radius: 5px; }
        .option-label { font-weight: bold; color: #667eea; margin-right: 8px; }
        .marks { float: right; background: #667eea; color: white; padding: 2px 8px; border-radius: 12px; font-size: 9pt; }
        .footer { margin-top: 40px; text-align: center; font-size: 9pt; color: #718096; border-top: 2px solid #e2e8f0; padding-top: 15px; }
        .answer-section { margin-top: 30px; page-break-before: always; }
        .answer-section h2 { text-align: center; color: #667eea; margin-bottom: 20px; font-size: 16pt; }
        .answer-grid { display: table; width: 100%; border-collapse: collapse; }
        .answer-row { display: table-row; }
        .answer-cell { display: table-cell; padding: 8px; border: 1px solid #e2e8f0; text-align: center; font-size: 10pt; }
        .answer-header { background: #667eea; color: white; font-weight: bold; }
    </style>
</head>
<body>
    <div class="school-header">
        @if(file_exists(public_path('images/logo.png')))
            <img src="{{ public_path('images/logo.png') }}" alt="School Logo" class="school-logo">
        @endif
        <div class="school-name">{{ config('myacademy.school_name', 'MyAcademy International School') }}</div>
        @if(config('myacademy.school_address'))
            <div class="school-address">{{ config('myacademy.school_address') }}</div>
        @endif
        @if(config('myacademy.school_phone'))
            <div class="school-address">Tel: {{ config('myacademy.school_phone') }} | Email: {{ config('myacademy.school_email') }}</div>
        @endif
    </div>

    <div class="exam-title">
        <h1>{{ $exam->title }}</h1>
        <div style="font-size: 10pt;">Computer Based Test (CBT) Examination</div>
    </div>

    <div class="exam-info">
        <div class="info-row">
            <div class="info-label">Class</div>
            <div class="info-value">{{ $exam->schoolClass?->name ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Subject</div>
            <div class="info-value">{{ $exam->subject?->name ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Session / Term</div>
            <div class="info-value">{{ $exam->session ?? '-' }} / Term {{ $exam->term ?? '-' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Duration</div>
            <div class="info-value">{{ $exam->duration_minutes }} minutes</div>
        </div>
        <div class="info-row">
            <div class="info-label">Total Questions</div>
            <div class="info-value">{{ $exam->questions->count() }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Total Marks</div>
            <div class="info-value">{{ $exam->questions->sum('marks') }}</div>
        </div>
    </div>

    <div class="instructions">
        <strong>Instructions:</strong> Read each question carefully and select the best answer from the options provided. Theory questions should be answered in the space provided. Each question carries the marks indicated. Write your answers clearly.
    </div>

    @foreach ($exam->questions as $q)
        <div class="question">
            <div class="question-number">
                Question {{ $loop->iteration }}
                <span class="marks">{{ $q->marks }} mark{{ $q->marks > 1 ? 's' : '' }}</span>
            </div>
            <div class="question-text">{{ $q->prompt }}</div>
            @if ($q->type === 'theory')
                <div style="margin-top: 10px;">
                    <div style="font-weight: bold; color: #667eea; margin-bottom: 6px;">Answer:</div>
                    <div style="border-bottom: 1px solid #e2e8f0; height: 18px;"></div>
                    <div style="border-bottom: 1px solid #e2e8f0; height: 18px;"></div>
                    <div style="border-bottom: 1px solid #e2e8f0; height: 18px;"></div>
                </div>
            @else
                <div class="options">
                    @foreach ($q->options as $opt)
                        <div class="option">
                            <span class="option-label">{{ chr(65 + $loop->index) }}.</span>
                            <span>{{ $opt->label }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endforeach

    <div class="answer-section">
        <h2>Answer Sheet</h2>
        <div class="answer-grid">
            <div class="answer-row">
                <div class="answer-cell answer-header">Question</div>
                <div class="answer-cell answer-header">Answer</div>
                <div class="answer-cell answer-header">Question</div>
                <div class="answer-cell answer-header">Answer</div>
            </div>
            @for ($i = 0; $i < ceil($exam->questions->count() / 2); $i++)
                <div class="answer-row">
                    <div class="answer-cell">{{ $i + 1 }}</div>
                    <div class="answer-cell"></div>
                    @if (($i + 1 + ceil($exam->questions->count() / 2)) <= $exam->questions->count())
                        <div class="answer-cell">{{ $i + 1 + ceil($exam->questions->count() / 2) }}</div>
                        <div class="answer-cell"></div>
                    @else
                        <div class="answer-cell"></div>
                        <div class="answer-cell"></div>
                    @endif
                </div>
            @endfor
        </div>
    </div>

    <div class="footer">
        <strong>{{ config('myacademy.school_name', 'MyAcademy') }}</strong><br>
        Generated on {{ now()->format('F j, Y \a\t g:i A') }}
    </div>
</body>
</html>
