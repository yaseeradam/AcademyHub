{{-- Psychomotor Domain Partial --}}
@if($showPsychomotor ?? false)
@php
    $style = $opts['psychomotor_style'] ?? $rcOptions['psychomotor_style'] ?? 'progress';
    
    $traits = [];
    if (isset($psychomotorTraits) && !empty($psychomotorTraits)) {
        foreach($psychomotorTraits as $name => $rating) {
            $traits[] = ['name' => $name, 'rating' => $rating];
        }
    } else {
        $traits = [
            ['name' => 'Handwriting', 'rating' => 'Good'],
            ['name' => 'Verbal Fluency', 'rating' => 'Good'],
            ['name' => 'Games / Sports', 'rating' => 'Average'],
            ['name' => 'Craft / Drawing', 'rating' => 'Good'],
            ['name' => 'Musical Skills', 'rating' => 'Average'],
            ['name' => 'Punctuality', 'rating' => 'Excellent'],
            ['name' => 'Neatness', 'rating' => 'Good'],
            ['name' => 'Politeness / Courtesy', 'rating' => 'Excellent'],
            ['name' => 'Honesty', 'rating' => 'Excellent'],
            ['name' => 'Self-Control', 'rating' => 'Good'],
            ['name' => 'Attentiveness', 'rating' => 'Good'],
            ['name' => 'Relationship with Others', 'rating' => 'Excellent'],
        ];
    }
@endphp

<div style="border: 1.5px solid {{ $rcBorderColor ?? '#ccc' }}; border-radius: 4px; padding: 4px 6px; margin-bottom: 4px; background: {{ $rcBgLight ?? '#f9fafb' }};">
    <div style="font-size: 7.5px; font-weight: 800; color: {{ $rcTitleColor ?? '#374151' }}; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; text-align: center;">
        Psychomotor / Affective Skills
    </div>

    @if($style === 'grid')
        {{-- GRID MATRIX STYLE --}}
        @php
            $half = ceil(count($traits) / 2);
            $column1 = array_slice($traits, 0, $half);
            $column2 = array_slice($traits, $half);
        @endphp
        <div style="display: table; width: 100%;">
            <div style="display: table-row;">
                {{-- Column 1 --}}
                <div style="display: table-cell; width: 49%; padding-right: 1%;">
                    <table style="width: 100%; border-collapse: collapse; border: 1px solid {{ $rcBorderColor ?? '#e5e7eb' }}; font-size: 6.5px;">
                        <thead>
                            <tr style="background: {{ $rcBorderColor ?? '#bae6fd' }}; color: white;">
                                <th style="text-align: left; padding: 2px 4px; border: 1px solid {{ $rcBorderColor ?? '#e5e7eb' }}; font-weight: 800; font-size: 6px;">Trait</th>
                                <th style="width: 11%; text-align: center; padding: 2px 1px; border: 1px solid {{ $rcBorderColor ?? '#e5e7eb' }}; font-weight: 800; font-size: 6px;">E</th>
                                <th style="width: 11%; text-align: center; padding: 2px 1px; border: 1px solid {{ $rcBorderColor ?? '#e5e7eb' }}; font-weight: 800; font-size: 6px;">G</th>
                                <th style="width: 11%; text-align: center; padding: 2px 1px; border: 1px solid {{ $rcBorderColor ?? '#e5e7eb' }}; font-weight: 800; font-size: 6px;">A</th>
                                <th style="width: 11%; text-align: center; padding: 2px 1px; border: 1px solid {{ $rcBorderColor ?? '#e5e7eb' }}; font-weight: 800; font-size: 6px;">F</th>
                                <th style="width: 11%; text-align: center; padding: 2px 1px; border: 1px solid {{ $rcBorderColor ?? '#e5e7eb' }}; font-weight: 800; font-size: 6px;">P</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($column1 as $trait)
                                @php
                                    $rating = $trait['rating'];
                                    $isE = ($rating === 'Excellent' || $rating === '5');
                                    $isG = ($rating === 'Good' || $rating === '4');
                                    $isA = ($rating === 'Average' || $rating === '3');
                                    $isF = ($rating === 'Fair' || $rating === '2');
                                    $isP = ($rating === 'Poor' || $rating === '1');
                                @endphp
                                <tr style="background: white;">
                                    <td style="padding: 1.5px 4px; border: 1px solid #e2e8f0; font-weight: 700; color: {{ $rcLabelColor ?? '#374151' }}; font-size: 7px;">{{ $trait['name'] }}</td>
                                    <td style="text-align: center; border: 1px solid #e2e8f0; font-weight: 900; color: #059669; font-size: 8px;">@if($isE)✓@endif</td>
                                    <td style="text-align: center; border: 1px solid #e2e8f0; font-weight: 900; color: #2563eb; font-size: 8px;">@if($isG)✓@endif</td>
                                    <td style="text-align: center; border: 1px solid #e2e8f0; font-weight: 900; color: #d97706; font-size: 8px;">@if($isA)✓@endif</td>
                                    <td style="text-align: center; border: 1px solid #e2e8f0; font-weight: 900; color: #ea580c; font-size: 8px;">@if($isF)✓@endif</td>
                                    <td style="text-align: center; border: 1px solid #e2e8f0; font-weight: 900; color: #dc2626; font-size: 8px;">@if($isP)✓@endif</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Column 2 --}}
                <div style="display: table-cell; width: 49%; padding-left: 1%;">
                    <table style="width: 100%; border-collapse: collapse; border: 1px solid {{ $rcBorderColor ?? '#e5e7eb' }}; font-size: 6.5px;">
                        <thead>
                            <tr style="background: {{ $rcBorderColor ?? '#bae6fd' }}; color: white;">
                                <th style="text-align: left; padding: 2px 4px; border: 1px solid {{ $rcBorderColor ?? '#e5e7eb' }}; font-weight: 800; font-size: 6px;">Trait</th>
                                <th style="width: 11%; text-align: center; padding: 2px 1px; border: 1px solid {{ $rcBorderColor ?? '#e5e7eb' }}; font-weight: 800; font-size: 6px;">E</th>
                                <th style="width: 11%; text-align: center; padding: 2px 1px; border: 1px solid {{ $rcBorderColor ?? '#e5e7eb' }}; font-weight: 800; font-size: 6px;">G</th>
                                <th style="width: 11%; text-align: center; padding: 2px 1px; border: 1px solid {{ $rcBorderColor ?? '#e5e7eb' }}; font-weight: 800; font-size: 6px;">A</th>
                                <th style="width: 11%; text-align: center; padding: 2px 1px; border: 1px solid {{ $rcBorderColor ?? '#e5e7eb' }}; font-weight: 800; font-size: 6px;">F</th>
                                <th style="width: 11%; text-align: center; padding: 2px 1px; border: 1px solid {{ $rcBorderColor ?? '#e5e7eb' }}; font-weight: 800; font-size: 6px;">P</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($column2 as $trait)
                                @php
                                    $rating = $trait['rating'];
                                    $isE = ($rating === 'Excellent' || $rating === '5');
                                    $isG = ($rating === 'Good' || $rating === '4');
                                    $isA = ($rating === 'Average' || $rating === '3');
                                    $isF = ($rating === 'Fair' || $rating === '2');
                                    $isP = ($rating === 'Poor' || $rating === '1');
                                @endphp
                                <tr style="background: white;">
                                    <td style="padding: 1.5px 4px; border: 1px solid #e2e8f0; font-weight: 700; color: {{ $rcLabelColor ?? '#374151' }}; font-size: 7px;">{{ $trait['name'] }}</td>
                                    <td style="text-align: center; border: 1px solid #e2e8f0; font-weight: 900; color: #059669; font-size: 8px;">@if($isE)✓@endif</td>
                                    <td style="text-align: center; border: 1px solid #e2e8f0; font-weight: 900; color: #2563eb; font-size: 8px;">@if($isG)✓@endif</td>
                                    <td style="text-align: center; border: 1px solid #e2e8f0; font-weight: 900; color: #d97706; font-size: 8px;">@if($isA)✓@endif</td>
                                    <td style="text-align: center; border: 1px solid #e2e8f0; font-weight: 900; color: #ea580c; font-size: 8px;">@if($isF)✓@endif</td>
                                    <td style="text-align: center; border: 1px solid #e2e8f0; font-weight: 900; color: #dc2626; font-size: 8px;">@if($isP)✓@endif</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div style="font-size: 6px; text-align: right; color: #64748b; font-style: italic; margin-top: 2px;">
            Key: E = Excellent, G = Good, A = Average, F = Fair, P = Poor
        </div>

    @elseif($style === 'numeric')
        {{-- NUMERIC scale 1-5 BLOCKS --}}
        @php
            $chunked = array_chunk($traits, 2);
            $scoreMap = [
                'Excellent' => 5, '5' => 5,
                'Good' => 4, '4' => 4,
                'Average' => 3, '3' => 3,
                'Fair' => 2, '2' => 2,
                'Poor' => 1, '1' => 1
            ];
        @endphp
        <div style="display: table; width: 100%;">
            @foreach($chunked as $pair)
            <div style="display: table-row;">
                @foreach($pair as $trait)
                @php $score = $scoreMap[$trait['rating']] ?? 3; @endphp
                <div style="display: table-cell; width: 50%; padding: 1.5px 4px;">
                    <div style="background: white; border: 1px solid #e5e7eb; border-radius: 3px; padding: 3px 6px;">
                        <span style="font-size: 7px; color: {{ $rcLabelColor ?? '#374151' }}; font-weight: 700; display: inline-block; width: 50%;">{{ $trait['name'] }}</span>
                        <span style="font-size: 7px; font-weight: 600; color: #6b7280; display: inline-block; width: 45%; text-align: right; vertical-align: middle;">
                            @for($i = 1; $i <= 5; $i++)
                                @php
                                    $active = ($score === $i);
                                    $bg = $active ? ($i === 5 ? '#059669' : ($i === 4 ? '#2563eb' : ($i === 3 ? '#d97706' : ($i === 2 ? '#ea580c' : '#dc2626')))) : '#f3f4f6';
                                    $color = $active ? '#ffffff' : '#9ca3af';
                                @endphp
                                <span style="display: inline-block; width: 10px; height: 10px; line-height: 10px; text-align: center; background: {{ $bg }}; color: {{ $color }}; font-size: 6.5px; font-weight: 900; border-radius: 2px; margin-left: 1px;">{{ $i }}</span>
                            @endfor
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>

    @else
        {{-- PROGRESS BARS (DEFAULT STYLE) --}}
        @php $chunked = array_chunk($traits, 2); @endphp
        <div style="display: table; width: 100%;">
            @foreach($chunked as $pair)
            <div style="display: table-row;">
                @foreach($pair as $trait)
                <div style="display: table-cell; width: 50%; padding: 1.5px 4px;">
                    <div style="background: white; border: 1px solid #e5e7eb; border-radius: 3px; padding: 3px 7px;">
                        <span style="font-size: 7px; color: {{ $rcLabelColor ?? '#374151' }}; font-weight: 700; display: inline-block; width: 55%;">{{ $trait['name'] }}</span>
                        <span style="font-size: 7px; font-weight: 600; color: #6b7280; display: inline-block; width: 40%; text-align: right;">
                            {{ $trait['rating'] }}
                            <span style="display: inline-block; width: 36px; height: 5px; background: #e5e7eb; border-radius: 2px; overflow: hidden; vertical-align: middle; margin-left: 3px;">
                                <span style="display: block; height: 100%; border-radius: 2px;
                                    @if($trait['rating'] === 'Excellent' || $trait['rating'] === '5') background: #059669; width: 100%;
                                    @elseif($trait['rating'] === 'Good' || $trait['rating'] === '4') background: #2563eb; width: 75%;
                                    @elseif($trait['rating'] === 'Average' || $trait['rating'] === '3') background: #d97706; width: 50%;
                                    @elseif($trait['rating'] === 'Fair' || $trait['rating'] === '2') background: #ea580c; width: 35%;
                                    @else background: #dc2626; width: 20%;
                                    @endif
                                "></span>
                            </span>
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
            @endforeach
        </div>
    @endif
</div>
@endif
