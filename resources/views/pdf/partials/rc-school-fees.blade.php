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
<div style="border: 1.5px solid {{ $rcBorderColor ?? '#d97706' }}; border-radius: 4px; padding: 4px 6px; margin-bottom: 4px; background: {{ $rcBgLight ?? '#fff7ed' }};">
    <div style="font-size: 7.5px; font-weight: 800; color: {{ $rcTitleColor ?? '#92400e' }}; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; text-align: center;">
        School Fees for Next Term
    </div>
    <div style="font-size: 13px; font-weight: 900; color: {{ $rcTitleColor ?? '#92400e' }}; text-align: center; padding: 4px; background: white; border: 1px solid {{ $rcBorderColor ?? '#d97706' }}; border-radius: 3px; margin-bottom: 4px;">
        {!! $currencyHtml !!}{{ number_format($schoolFees['amount'], 2) }}
    </div>
    <div style="display: table; width: 100%;">
        @if($schoolFees['bank_name'] ?? null)
        <div style="display: table-cell; padding: 2px 5px; vertical-align: top;">
            <span style="font-size: 6.5px; color: {{ $rcTitleColor ?? '#92400e' }}; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 1px;">Bank Name</span>
            <span style="font-size: 8.5px; font-weight: 800; color: {{ $rcLabelColor ?? '#78350f' }};">{{ $schoolFees['bank_name'] }}</span>
        </div>
        @endif
        @if($schoolFees['account_number'] ?? null)
        <div style="display: table-cell; padding: 2px 5px; vertical-align: top;">
            <span style="font-size: 6.5px; color: {{ $rcTitleColor ?? '#92400e' }}; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 1px;">Account Number</span>
            <span style="font-size: 8.5px; font-weight: 800; color: {{ $rcLabelColor ?? '#78350f' }};">{{ $schoolFees['account_number'] }}</span>
        </div>
        @endif
        @if($schoolFees['account_name'] ?? null)
        <div style="display: table-cell; padding: 2px 5px; vertical-align: top;">
            <span style="font-size: 6.5px; color: {{ $rcTitleColor ?? '#92400e' }}; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 1px;">Account Name</span>
            <span style="font-size: 8.5px; font-weight: 800; color: {{ $rcLabelColor ?? '#78350f' }};">{{ $schoolFees['account_name'] }}</span>
        </div>
        @endif
    </div>
</div>
@endif
