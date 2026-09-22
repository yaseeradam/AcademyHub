<!doctype html>
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>{{ $transaction->receipt_number }}</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: DejaVu Sans, Arial, sans-serif;
                font-size: 11px;
                color: #1f2937;
                padding: 20px;
                background: #ffffff;
            }
            .receipt-container {
                max-width: 700px;
                margin: 0 auto;
                border: 3px double #3b82f6;
                padding: 20px;
                background: white;
            }
            .header {
                text-align: center;
                border-bottom: 3px solid #3b82f6;
                padding-bottom: 15px;
                margin-bottom: 20px;
                position: relative;
            }
            .header-flex {
                display: table;
                width: 100%;
            }
            .header-logo {
                display: table-cell;
                width: 80px;
                vertical-align: middle;
            }
            .header-center {
                display: table-cell;
                vertical-align: middle;
                text-align: center;
            }
            .logo {
                width: 70px;
                height: 70px;
                object-fit: contain;
                border: 2px solid #3b82f6;
                border-radius: 50%;
                padding: 5px;
                background: white;
            }
            .school-name {
                font-size: 22px;
                font-weight: 700;
                color: #1e40af;
                margin-bottom: 3px;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            .school-info {
                font-size: 9px;
                color: #6b7280;
                margin-bottom: 3px;
            }
            .receipt-title {
                font-size: 16px;
                font-weight: 700;
                color: white;
                background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                padding: 8px;
                margin-top: 10px;
                text-transform: uppercase;
                letter-spacing: 2px;
                border-radius: 4px;
            }
            .receipt-number-box {
                background: #dbeafe;
                border: 2px solid #3b82f6;
                border-radius: 8px;
                padding: 12px;
                margin-bottom: 15px;
                text-align: center;
            }
            .receipt-number-label {
                font-size: 9px;
                color: #1e40af;
                font-weight: 600;
                text-transform: uppercase;
                margin-bottom: 4px;
            }
            .receipt-number {
                font-size: 20px;
                font-weight: 700;
                color: #1f2937;
                letter-spacing: 1px;
            }
            .status-badge {
                display: inline-block;
                padding: 4px 12px;
                border-radius: 999px;
                font-weight: 700;
                font-size: 10px;
                margin-top: 5px;
            }
            .badge-paid {
                background: #d1fae5;
                color: #065f46;
                border: 1px solid #10b981;
            }
            .badge-void {
                background: #fee2e2;
                color: #991b1b;
                border: 1px solid #ef4444;
            }
            .info-section {
                background: #f9fafb;
                border: 2px solid #e5e7eb;
                border-radius: 6px;
                padding: 15px;
                margin-bottom: 15px;
            }
            .info-title {
                font-size: 11px;
                font-weight: 700;
                color: #1f2937;
                margin-bottom: 10px;
                padding-bottom: 5px;
                border-bottom: 2px solid #3b82f6;
            }
            .info-grid {
                display: table;
                width: 100%;
            }
            .info-row {
                display: table-row;
            }
            .info-label {
                display: table-cell;
                width: 40%;
                padding: 6px 10px 6px 0;
                color: #6b7280;
                font-weight: 600;
                font-size: 10px;
            }
            .info-value {
                display: table-cell;
                padding: 6px 0;
                font-weight: 700;
                color: #1f2937;
                font-size: 11px;
            }
            .amount-box {
                background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
                border: 3px solid #10b981;
                border-radius: 8px;
                padding: 20px;
                margin: 20px 0;
                text-align: center;
            }
            .amount-label {
                font-size: 10px;
                color: #065f46;
                font-weight: 700;
                text-transform: uppercase;
                margin-bottom: 8px;
            }
            .amount-value {
                font-size: 32px;
                font-weight: 700;
                color: #1f2937;
                letter-spacing: 1px;
            }
            .amount-words {
                font-size: 10px;
                color: #065f46;
                font-weight: 600;
                margin-top: 8px;
                font-style: italic;
            }
            .payment-details {
                background: #fef3c7;
                border: 2px solid #f59e0b;
                border-radius: 6px;
                padding: 12px;
                margin-bottom: 15px;
            }
            .payment-title {
                font-size: 10px;
                font-weight: 700;
                color: #92400e;
                margin-bottom: 8px;
            }
            .payment-grid {
                display: table;
                width: 100%;
            }
            .payment-item {
                display: table-cell;
                width: 50%;
                padding: 5px;
            }
            .payment-item-label {
                font-size: 8px;
                color: #92400e;
                font-weight: 600;
                margin-bottom: 2px;
            }
            .payment-item-value {
                font-size: 10px;
                font-weight: 700;
                color: #1f2937;
            }
            .void-section {
                background: #fee2e2;
                border: 3px solid #ef4444;
                border-radius: 6px;
                padding: 12px;
                margin-bottom: 15px;
            }
            .void-title {
                font-size: 11px;
                font-weight: 700;
                color: #991b1b;
                margin-bottom: 8px;
                text-transform: uppercase;
            }
            .notes-section {
                background: #ede9fe;
                border: 2px solid #8b5cf6;
                border-radius: 6px;
                padding: 12px;
                margin-bottom: 15px;
            }
            .notes-title {
                font-size: 10px;
                font-weight: 700;
                color: #5b21b6;
                margin-bottom: 6px;
            }
            .notes-content {
                font-size: 9px;
                color: #1f2937;
                line-height: 1.5;
            }
            .signature-section {
                margin-top: 30px;
                display: table;
                width: 100%;
            }
            .signature {
                display: table-cell;
                width: 50%;
                text-align: center;
                padding: 10px;
            }
            .signature-line {
                border-top: 2px solid #1f2937;
                margin-top: 40px;
                padding-top: 5px;
                font-size: 10px;
                font-weight: 700;
            }
            .signature-label {
                font-size: 8px;
                color: #6b7280;
                margin-top: 2px;
            }
            .footer {
                margin-top: 20px;
                padding-top: 15px;
                border-top: 3px solid #3b82f6;
                text-align: center;
                font-size: 8px;
                color: #6b7280;
            }
            .footer-note {
                background: #fef3c7;
                border: 1px dashed #f59e0b;
                padding: 8px;
                margin-top: 10px;
                font-size: 9px;
                color: #92400e;
                font-weight: 600;
                text-align: center;
            }
            .void-watermark {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%) rotate(-45deg);
                font-size: 120px;
                font-weight: 800;
                color: #ef4444;
                opacity: 0.1;
                letter-spacing: 10px;
                z-index: -1;
            }
            .watermark {
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                z-index: -1;
                opacity: 0.03;
                width: 400px;
                height: 400px;
            }
        </style>
    </head>
    <body>
        @if ($transaction->is_void)
            <div class="void-watermark">VOID</div>
        @endif

        @if(config('academyhub.school_logo'))
            <div class="watermark">
                <img src="{{ storage_path('app/public/' . config('academyhub.school_logo')) }}" alt="Watermark" style="width: 100%; height: 100%; object-fit: contain;" />
            </div>
        @endif

        <div class="receipt-container">
            <div class="header">
                <div class="header-flex">
                    @if(config('academyhub.school_logo'))
                        <div class="header-logo">
                            <img src="{{ storage_path('app/public/' . config('academyhub.school_logo')) }}" alt="Logo" class="logo" />
                        </div>
                    @endif
                    <div class="header-center">
                        <div class="school-name">{{ config('academyhub.school_name', config('app.name', 'AcademyHub')) }}</div>
                        @if(config('academyhub.school_address'))
                            <div class="school-info">{{ config('academyhub.school_address') }}</div>
                        @endif
                        @if(config('academyhub.school_phone') || config('academyhub.school_email'))
                            <div class="school-info">
                                {{ config('academyhub.school_phone') }}
                                @if(config('academyhub.school_phone') && config('academyhub.school_email')) • @endif
                                {{ config('academyhub.school_email') }}
                            </div>
                        @endif
                        <div class="receipt-title">Official Payment Receipt</div>
                    </div>
                    @if(config('academyhub.school_logo'))
                        <div class="header-logo">
                            <img src="{{ storage_path('app/public/' . config('academyhub.school_logo')) }}" alt="Logo" class="logo" />
                        </div>
                    @endif
                </div>
            </div>

            <div class="receipt-number-box">
                <div class="receipt-number-label">Receipt Number</div>
                <div class="receipt-number">{{ $transaction->receipt_number }}</div>
                <div>
                    <span class="status-badge {{ $transaction->is_void ? 'badge-void' : 'badge-paid' }}">
                        {{ $transaction->is_void ? '✗ VOID' : '✓ PAID' }}
                    </span>
                </div>
            </div>

            @if ($transaction->is_void)
                <div class="void-section">
                    <div class="void-title">⚠ This Receipt Has Been Voided</div>
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-label">Voided Date:</div>
                            <div class="info-value">{{ $transaction->voided_at?->format('F j, Y \a\t g:i A') ?: '—' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Reason:</div>
                            <div class="info-value">{{ $transaction->void_reason ?: 'No reason provided' }}</div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="info-section">
                <div class="info-title">STUDENT INFORMATION</div>
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-label">Student Name:</div>
                        <div class="info-value">{{ $transaction->student?->full_name ?: 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Admission Number:</div>
                        <div class="info-value">{{ $transaction->student?->admission_number ?: 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Class:</div>
                        <div class="info-value">{{ $transaction->student?->schoolClass?->name ?: 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Section:</div>
                        <div class="info-value">{{ $transaction->student?->section?->name ?: 'N/A' }}</div>
                    </div>
                </div>
            </div>

            <div class="payment-details">
                <div class="payment-title">PAYMENT DETAILS</div>
                <div class="payment-grid">
                    <div class="payment-item">
                        <div class="payment-item-label">Payment Date:</div>
                        <div class="payment-item-value">{{ $transaction->date?->format('F j, Y') }}</div>
                    </div>
                    <div class="payment-item">
                        <div class="payment-item-label">Payment Method:</div>
                        <div class="payment-item-value">{{ $transaction->payment_method ?: 'Cash' }}</div>
                    </div>
                </div>
                <div class="payment-grid" style="margin-top: 8px;">
                    <div class="payment-item">
                        <div class="payment-item-label">Category:</div>
                        <div class="payment-item-value">{{ $transaction->category }}</div>
                    </div>
                    <div class="payment-item">
                        <div class="payment-item-label">Session / Term:</div>
                        <div class="payment-item-value">
                            {{ $transaction->session ?: '—' }}
                            @if ($transaction->term)
                                / Term {{ $transaction->term }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="amount-box">
                <div class="amount-label">Amount Paid</div>
                @php
                    $currencySymbol = config('academyhub.currency_symbol', '₦');
                    if ($currencySymbol === '₦' || html_entity_decode($currencySymbol) === '₦') {
                        $currencyHtml = '<span style="font-family: DejaVu Sans, sans-serif;">&#8358;</span>';
                    } else {
                        $currencyHtml = '<span style="font-family: DejaVu Sans, sans-serif;">' . e($currencySymbol) . '</span>';
                    }
                @endphp
                <div class="amount-value">{!! $currencyHtml !!}{{ number_format((float) $transaction->amount_paid, 2) }}</div>
                <div class="amount-words">
                    ({{ \App\Support\MoneyWords::forReceipt($transaction->amount_paid, config('academyhub.currency_name', 'Naira'), config('academyhub.currency_subunit', 'Kobo')) }})
                </div>
            </div>

            <div class="notes-section">
                <div class="notes-title">IMPORTANT NOTES:</div>
                <div class="notes-content">
                    • This is an official receipt issued by {{ config('academyhub.school_name', config('app.name', 'AcademyHub')) }}<br/>
                    • Please keep this receipt for your records<br/>
                    • This receipt is valid only when stamped and signed<br/>
                    • For any queries, contact the school bursar
                </div>
            </div>

            <div class="signature-section">
                <div class="signature">
                    <div class="signature-line">Bursar</div>
                    <div class="signature-label">Signature & Date</div>
                </div>
                <div class="signature">
                    <div class="signature-line">Principal</div>
                    <div class="signature-label">Signature & Stamp</div>
                </div>
            </div>

            <div class="footer">
                Generated on {{ now()->format('l, F j, Y \a\t g:i A') }}<br/>
                {{ config('academyhub.school_name', config('app.name', 'AcademyHub')) }} • Powered by AcademyHub SMS
            </div>

            <div class="footer-note">
                This is a computer-generated receipt and is valid without signature if generated electronically
            </div>
        </div>
    </body>
</html>
