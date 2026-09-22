@php
    $schoolName = config('academyhub.school_name', config('app.name', 'AcademyHub'));
    $logo = config('academyhub.school_logo');
    $logoPath = $logo ? public_path('uploads/'.str_replace('\\', '/', $logo)) : null;

    $issuedOn = $certificate?->issued_on ?? $certificate?->issue_date ?? now();
@endphp
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>{{ $certificate?->title ?? 'Certificate' }}</title>
        <style>
            @page { margin: 20px; }
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: DejaVu Serif, Georgia, serif; color: #1e293b; }

            .page {
                position: relative;
                width: 100%;
                height: 100%;
                border: 8px solid #e2e8f0;
                padding: 30px;
            }
            .inner {
                position: relative;
                border: 2px solid #e2e8f0;
                padding: 40px;
                height: 100%;
                background: #ffffff;
            }
            
            .content { position: relative; text-align: center; height: 100%; display: flex; flex-direction: column; justify-content: center; }

            .logo-section {
                margin-bottom: 20px;
            }
            .logo-icon {
                width: 50px;
                height: 50px;
                margin: 0 auto 10px;
                background: #f1f5f9;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .school-name {
                font-size: 14px;
                font-weight: 700;
                letter-spacing: 3px;
                text-transform: uppercase;
                color: #94a3b8;
            }

            .title {
                font-size: 48px;
                font-weight: 700;
                font-style: italic;
                margin: 20px 0;
                color: #1e293b;
            }
            
            .presented {
                font-size: 11px;
                font-weight: 600;
                letter-spacing: 2px;
                text-transform: uppercase;
                color: #64748b;
                margin-bottom: 20px;
            }

            .student {
                font-size: 36px;
                font-weight: 700;
                font-style: italic;
                color: #334155;
                border-bottom: 2px solid #e2e8f0;
                display: inline-block;
                padding: 5px 40px;
                margin-bottom: 30px;
            }

            .type-label {
                font-size: 12px;
                font-weight: 600;
                text-transform: uppercase;
                color: #475569;
                margin-bottom: 8px;
            }
            
            .description {
                font-size: 11px;
                font-style: italic;
                color: #94a3b8;
                max-width: 400px;
                margin: 0 auto;
            }

            .footer {
                position: absolute;
                left: 40px;
                right: 40px;
                bottom: 40px;
                display: table;
                width: calc(100% - 80px);
                border-top: 1px solid #f1f5f9;
                padding-top: 15px;
            }
            .footer-cell {
                display: table-cell;
                width: 33.33%;
                text-align: center;
                vertical-align: top;
            }
            .sig-line {
                width: 120px;
                border-top: 1px solid #cbd5e1;
                margin: 0 auto;
            }
            .sig-label {
                margin-top: 5px;
                font-size: 9px;
                color: #94a3b8;
            }
            .seal {
                width: 60px;
                height: 60px;
                margin: 0 auto;
                background: #fef3c7;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                color: #eab308;
            }
        </style>
    </head>
    <body>
        <div class="page">
            <div class="inner">
                <div class="content">
                    <div class="logo-section">
                        <div class="logo-icon">🎓</div>
                        <div class="school-name">{{ $schoolName }}</div>
                    </div>

                    <div class="title">{{ $certificate?->title ?? 'Certificate of Achievement' }}</div>
                    
                    <div class="presented">This is to certify that</div>
                    
                    <div class="student">{{ $student?->full_name }}</div>

                    <div>
                        <div class="type-label">
                            @if($certificate?->type === 'Achievement')
                                OUTSTANDING ACADEMIC MERIT
                            @elseif($certificate?->type === 'Attendance')
                                PERFECT ATTENDANCE RECORD
                            @elseif($certificate?->type === 'Excellence')
                                EXCELLENCE IN ATHLETICISM
                            @else
                                ACADEMIC EXCELLENCE
                            @endif
                        </div>
                        <div class="description">{{ $certificate?->description ?? 'Outstanding performance' }}</div>
                    </div>
                </div>

                <div class="footer">
                    <div class="footer-cell">
                        <div class="sig-line"></div>
                        <div class="sig-label">Principal</div>
                    </div>
                    <div class="footer-cell">
                        <div class="seal">✓</div>
                    </div>
                    <div class="footer-cell">
                        <div class="sig-line"></div>
                        <div class="sig-label">{{ $issuedOn?->format('M d, Y') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
