{{-- Shared inline student info section --}}
{{-- Theme vars: $siBorderColor, $siBgColor, $siLabelColor, $siValueColor, $siDotColor --}}
@php
    $siBorderColor = $siBorderColor ?? '#d97706';
    $siBgColor = $siBgColor ?? '#fffbeb';
    $siLabelColor = $siLabelColor ?? '#92400e';
    $siValueColor = $siValueColor ?? '#1f2937';
    $siDotColor = $siDotColor ?? '#fbbf24';
    $rcPhotoUri = $student->passport_photo_data_uri;
@endphp
<div style="border: 1.5px solid {{ $siBorderColor }}; border-radius: 4px; padding: 3px 5px; margin-bottom: {{ $secMargin ?? '3px' }}; background: {{ $siBgColor }};">
    <table cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="vertical-align: middle; padding-right: 4px;">
                <table cellpadding="0" cellspacing="0" style="width: 100%; table-layout: fixed; border-collapse: collapse;">
                    <colgroup>
                        <col style="width: 38%;">
                        <col style="width: 32%;">
                        <col style="width: 30%;">
                    </colgroup>
                    <tbody>
                        <tr style="border-bottom: 1px dotted {{ $siDotColor }};">
                            <td style="width: 38%; padding: 2px 3px; vertical-align: middle; overflow: hidden; white-space: nowrap;">
                                <span style="color: {{ $siLabelColor }}; font-weight: 700; font-size: 6.5px; text-transform: uppercase;">Student Name:</span>
                                <span style="font-weight: 700; color: {{ $siValueColor }}; font-size: 8px;"> {{ $student->full_name }}</span>
                            </td>
                            <td style="width: 32%; padding: 2px 3px; vertical-align: middle; overflow: hidden; white-space: nowrap;">
                                <span style="color: {{ $siLabelColor }}; font-weight: 700; font-size: 6.5px; text-transform: uppercase;">Admission No:</span>
                                <span style="font-weight: 700; color: {{ $siValueColor }}; font-size: 8px;"> {{ $student->admission_number }}</span>
                            </td>
                            <td style="width: 30%; padding: 2px 3px; vertical-align: middle; overflow: hidden; white-space: nowrap;">
                                <span style="color: {{ $siLabelColor }}; font-weight: 700; font-size: 6.5px; text-transform: uppercase;">Class / Section:</span>
                                <span style="font-weight: 700; color: {{ $siValueColor }}; font-size: 8px;"> {{ $student->schoolClass?->name }} {{ $student->section?->name ? '— ' . $student->section->name : '' }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 38%; padding: 2px 3px; vertical-align: middle; overflow: hidden; white-space: nowrap;">
                                <span style="color: {{ $siLabelColor }}; font-weight: 700; font-size: 6.5px; text-transform: uppercase;">Gender:</span>
                                <span style="font-weight: 700; color: {{ $siValueColor }}; font-size: 8px;"> {{ $student->gender ?? 'N/A' }}</span>
                            </td>
                            <td style="width: 32%; padding: 2px 3px; vertical-align: middle; overflow: hidden; white-space: nowrap;">
                                <span style="color: {{ $siLabelColor }}; font-weight: 700; font-size: 6.5px; text-transform: uppercase;">Date of Birth:</span>
                                <span style="font-weight: 700; color: {{ $siValueColor }}; font-size: 8px;"> {{ $student->dob ? \Carbon\Carbon::parse($student->dob)->format('d M, Y') : ($student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('d M, Y') : 'N/A') }}</span>
                            </td>
                            <td style="width: 30%; padding: 2px 3px; vertical-align: middle; overflow: hidden; white-space: nowrap;">
                                <span style="color: {{ $siLabelColor }}; font-weight: 700; font-size: 6.5px; text-transform: uppercase;">No. in Class:</span>
                                <span style="font-weight: 700; color: {{ $siValueColor }}; font-size: 8px;"> {{ $totalStudents ?? 'N/A' }}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
            <td style="width: 44px; text-align: center; vertical-align: middle;">
                @if($rcPhotoUri)
                    <img src="{{ $rcPhotoUri }}"
                        alt="Photo" style="width: 40px; height: 46px; border: 1.5px solid {{ $siBorderColor }}; border-radius: 3px; object-fit: cover; display: block; margin: 0 auto;" />
                @else
                    <div style="width: 40px; height: 46px; border: 1.5px solid {{ $siBorderColor }}; border-radius: 3px; background: #f8fafc; text-align: center; line-height: 44px; font-size: 13px; font-weight: bold; color: {{ $siBorderColor }}; margin: 0 auto;">
                        {{ strtoupper(substr($student->first_name ?? 'S', 0, 1)) }}
                    </div>
                @endif
            </td>
        </tr>
    </table>
</div>
