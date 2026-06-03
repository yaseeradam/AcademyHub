{{-- School Fees Partial --}}
@if(($showSchoolFees ?? false) && !empty($schoolFees))
@php
    $feeCurrency = $schoolFees['currency'] ?? '₦';
    if ($feeCurrency === '₦' || html_entity_decode($feeCurrency) === '₦') {
        $currencyHtml = '<span style="font-family: DejaVu Sans, sans-serif;">&#8358;</span>';
    } else {
        $currencyHtml = '<span style="font-family: DejaVu Sans, sans-serif;">' . e($feeCurrency) . '</span>';
    }
@endphp
<div style="border: 3px solid {{ $rcBorderColor ?? '#d97706' }}; border-radius: 10px; padding: 12px; margin-bottom: 14px; background: {{ $rcBgLight ?? '#fff7ed' }};">
    <div style="font-size: 10px; font-weight: 900; color: {{ $rcTitleColor ?? '#92400e' }}; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 8px; text-align: center;">
        School Fees for Next Term
    </div>
    <div style="font-size: 16px; font-weight: 900; color: {{ $rcTitleColor ?? '#92400e' }}; text-align: center; padding: 8px; background: white; border: 2px solid {{ $rcBorderColor ?? '#d97706' }}; border-radius: 8px; margin-bottom: 8px;">
        {!! $currencyHtml !!}{{ number_format($schoolFees['amount'], 2) }}
    </div>
    <div style="display: table; width: 100%;">
        @if($schoolFees['bank_name'] ?? null)
        <div style="display: table-cell; padding: 4px 8px; vertical-align: top;">
            <span style="font-size: 7px; color: {{ $rcTitleColor ?? '#92400e' }}; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 2px;">Bank Name</span>
            <span style="font-size: 10px; font-weight: 800; color: {{ $rcLabelColor ?? '#78350f' }};">{{ $schoolFees['bank_name'] }}</span>
        </div>
        @endif
        @if($schoolFees['account_number'] ?? null)
        <div style="display: table-cell; padding: 4px 8px; vertical-align: top;">
            <span style="font-size: 7px; color: {{ $rcTitleColor ?? '#92400e' }}; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 2px;">Account Number</span>
            <span style="font-size: 10px; font-weight: 800; color: {{ $rcLabelColor ?? '#78350f' }};">{{ $schoolFees['account_number'] }}</span>
        </div>
        @endif
        @if($schoolFees['account_name'] ?? null)
        <div style="display: table-cell; padding: 4px 8px; vertical-align: top;">
            <span style="font-size: 7px; color: {{ $rcTitleColor ?? '#92400e' }}; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 2px;">Account Name</span>
            <span style="font-size: 10px; font-weight: 800; color: {{ $rcLabelColor ?? '#78350f' }};">{{ $schoolFees['account_name'] }}</span>
        </div>
        @endif
    </div>
</div>
@endif
