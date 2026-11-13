{{-- Shared inline student info section --}}
{{-- Theme vars: $siBorderColor, $siBgColor, $siLabelColor, $siValueColor, $siDotColor --}}
@php
    $siBorderColor = $siBorderColor ?? '#d97706';
    $siBgColor = $siBgColor ?? '#fffbeb';
    $siLabelColor = $siLabelColor ?? '#92400e';
    $siValueColor = $siValueColor ?? '#1f2937';
    $siDotColor = $siDotColor ?? '#fbbf24';
@endphp
<div style="border: 1.5px solid {{ $siBorderColor }}; border-radius: 4px; padding: 3px 5px; margin-bottom: {{ $secMargin ?? '3px' }}; background: {{ $siBgColor }};">
    <div style="display: table; width: 100%;">
        <div style="display: table-cell; vertical-align: middle;">
            {{-- Row 1: Name, Admission No, Class/Section --}}
            <div style="display: table; width: 100%; border-bottom: 1px dotted {{ $siDotColor }};">
                <div style="display: table-cell; padding: 1px 3px; vertical-align: middle;">
                    <span style="color: {{ $siLabelColor }}; font-weight: 700; font-size: 6.5px; text-transform: uppercase; white-space: nowrap;">Student Name:</span>
                    <span style="font-weight: 700; color: {{ $siValueColor }}; font-size: 8px;"> {{ $student->full_name }}</span>
                </div>
                <div style="display: table-cell; padding: 1px 3px; vertical-align: middle;">
                    <span style="color: {{ $siLabelColor }}; font-weight: 700; font-size: 6.5px; text-transform: uppercase; white-space: nowrap;">Admission No:</span>
                    <span style="font-weight: 700; color: {{ $siValueColor }}; font-size: 8px;"> {{ $student->admission_number }}</span>
                </div>
                <div style="display: table-cell; padding: 1px 3px; vertical-align: middle;">
                    <span style="color: {{ $siLabelColor }}; font-weight: 700; font-size: 6.5px; text-transform: uppercase; white-space: nowrap;">Class / Section:</span>
                    <span style="font-weight: 700; color: {{ $siValueColor }}; font-size: 8px;"> {{ $student->schoolClass?->name }} {{ $student->section?->name ? '— ' . $student->section->name : '' }}</span>
                </div>
            </div>
            {{-- Row 2: Gender, DOB, No. in Class --}}
            <div style="display: table; width: 100%;">
                <div style="display: table-cell; padding: 1px 3px; vertical-align: middle;">
                    <span style="color: {{ $siLabelColor }}; font-weight: 700; font-size: 6.5px; text-transform: uppercase; white-space: nowrap;">Gender:</span>
                    <span style="font-weight: 700; color: {{ $siValueColor }}; font-size: 8px;"> {{ $student->gender ?? 'N/A' }}</span>
                </div>
                <div style="display: table-cell; padding: 1px 3px; vertical-align: middle;">
                    <span style="color: {{ $siLabelColor }}; font-weight: 700; font-size: 6.5px; text-transform: uppercase; white-space: nowrap;">Date of Birth:</span>
                    <span style="font-weight: 700; color: {{ $siValueColor }}; font-size: 8px;"> {{ $student->dob ? \Carbon\Carbon::parse($student->dob)->format('d M, Y') : ($student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d M, Y') : 'N/A') }}</span>
                </div>
                <div style="display: table-cell; padding: 1px 3px; vertical-align: middle;">
                    <span style="color: {{ $siLabelColor }}; font-weight: 700; font-size: 6.5px; text-transform: uppercase; white-space: nowrap;">No. in Class:</span>
                    <span style="font-weight: 700; color: {{ $siValueColor }}; font-size: 8px;"> {{ $totalStudents ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
        @php
            $rcPhotoUri = $student->passport_photo_data_uri;
        @endphp
        <div style="display: table-cell; width: 48px; text-align: center; vertical-align: middle; padding-left: 3px;">
            @if($rcPhotoUri)
                <img src="{{ $rcPhotoUri }}"
                    alt="Photo" style="width: 40px; height: 46px; border: 1.5px solid {{ $siBorderColor }}; border-radius: 3px; object-fit: cover;" />
            @else
                <div style="width: 40px; height: 46px; border: 1.5px solid {{ $siBorderColor }}; border-radius: 3px; background: #f8fafc; text-align: center; line-height: 44px; font-size: 13px; font-weight: bold; color: {{ $siBorderColor }};">
                    {{ strtoupper(substr($student->first_name ?? 'S', 0, 1)) }}
                </div>
            @endif
        </div>
    </div>
</div>
