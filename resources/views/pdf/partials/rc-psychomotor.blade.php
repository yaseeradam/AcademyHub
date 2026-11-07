{{-- Psychomotor & Affective Domain Partial --}}
@if($showPsychomotor ?? false)
@php
    $style = $opts['psychomotor_style'] ?? $rcOptions['psychomotor_style'] ?? 'progress';
    $studentClassId = $student->class_id ?? null;
    $profile = \App\Support\PsychomotorService::getTraitsForClass($studentClassId);
    $domainMap = $profile['trait_domain'] ?? [];
    $scale = $profile['scale'] ?? \App\Support\PsychomotorService::SCALE_5_POINT;

    $affectiveTraits = [];
    $psychomotorTraitsList = [];

    if (isset($psychomotorTraits) && !empty($psychomotorTraits)) {
        foreach ($psychomotorTraits as $name => $rating) {
            $domain = $domainMap[$name] ?? (in_array($name, $profile['affective'] ?? [], true) ? 'affective' : 'psychomotor');
            if ($domain === 'affective') {
                $affectiveTraits[] = ['name' => $name, 'rating' => $rating];
            } else {
                $psychomotorTraitsList[] = ['name' => $name, 'rating' => $rating];
            }
        }
    }

    if (empty($affectiveTraits) && empty($psychomotorTraitsList)) {
        $defaultVal = \App\Support\PsychomotorService::defaultRating($scale);
        foreach (($profile['affective'] ?? []) as $name) {
            $affectiveTraits[] = ['name' => $name, 'rating' => $defaultVal];
        }
        foreach (($profile['psychomotor'] ?? []) as $name) {
            $psychomotorTraitsList[] = ['name' => $name, 'rating' => $defaultVal];
        }
    }
@endphp

<div style="border: 1px solid {{ $rcBorderColor ?? '#ccc' }}; border-radius: 4px; padding: 4px; margin-bottom: 4px; background: {{ $rcBgLight ?? '#f9fafb' }};">
    <div style="display: table; width: 100%; border-spacing: 0;">
        <div style="display: table-row;">
            {{-- COLUMN 1: AFFECTIVE DOMAIN --}}
            <div style="display: table-cell; width: 49.5%; vertical-align: top; padding-right: 2px;">
                <div style="border: 1px solid {{ $rcBorderColor ?? '#e2e8f0' }}; border-radius: 3px; background: white; overflow: hidden;">
                    <div style="background: {{ $rcBorderColor ?? '#1e3a5f' }}; color: white; padding: 2.5px 4px; font-size: 6.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; text-align: center;">
                        Affective &amp; Behavioural Domain
                    </div>

                    @if($style === 'grid')
                        <table style="width: 100%; border-collapse: collapse; font-size: 6px;">
                            <thead>
                                <tr style="background: #f1f5f9; color: #334155; border-bottom: 1px solid #e2e8f0;">
                                    <th style="text-align: left; padding: 2px 4px; font-weight: 800;">Trait</th>
                                    <th style="width: 10%; text-align: center; padding: 2px 1px; font-weight: 800;">5</th>
                                    <th style="width: 10%; text-align: center; padding: 2px 1px; font-weight: 800;">4</th>
                                    <th style="width: 10%; text-align: center; padding: 2px 1px; font-weight: 800;">3</th>
                                    <th style="width: 10%; text-align: center; padding: 2px 1px; font-weight: 800;">2</th>
                                    <th style="width: 10%; text-align: center; padding: 2px 1px; font-weight: 800;">1</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($affectiveTraits as $trait)
                                    @php $sc = \App\Support\PsychomotorService::ratingToScore($trait['rating']); @endphp
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding: 1.5px 4px; font-weight: 700; color: {{ $rcLabelColor ?? '#374151' }};">{{ $trait['name'] }}</td>
                                        <td style="text-align: center; font-weight: 900; color: #059669; font-size: 7px;">@if($sc === 5)&check;@endif</td>
                                        <td style="text-align: center; font-weight: 900; color: #2563eb; font-size: 7px;">@if($sc === 4)&check;@endif</td>
                                        <td style="text-align: center; font-weight: 900; color: #d97706; font-size: 7px;">@if($sc === 3)&check;@endif</td>
                                        <td style="text-align: center; font-weight: 900; color: #ea580c; font-size: 7px;">@if($sc === 2)&check;@endif</td>
                                        <td style="text-align: center; font-weight: 900; color: #dc2626; font-size: 7px;">@if($sc === 1)&check;@endif</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @elseif($style === 'numeric')
                        <div style="padding: 2px 4px;">
                            @foreach($affectiveTraits as $trait)
                                @php $sc = \App\Support\PsychomotorService::ratingToScore($trait['rating']); @endphp
                                <div style="display: table; width: 100%; padding: 1.5px 0; border-bottom: 1px dotted #e2e8f0;">
                                    <div style="display: table-cell; font-size: 6.5px; font-weight: 700; color: {{ $rcLabelColor ?? '#374151' }}; vertical-align: middle;">{{ $trait['name'] }}</div>
                                    <div style="display: table-cell; text-align: right; vertical-align: middle; width: 62px;">
                                        @for($i = 1; $i <= 5; $i++)
                                            @php
                                                $active = ($sc === $i);
                                                $bg = $active ? ($i === 5 ? '#059669' : ($i === 4 ? '#2563eb' : ($i === 3 ? '#d97706' : ($i === 2 ? '#ea580c' : '#dc2626')))) : '#f1f5f9';
                                                $color = $active ? '#ffffff' : '#94a3b8';
                                            @endphp
                                            <span style="display: inline-block; width: 9px; height: 9px; line-height: 9px; text-align: center; background: {{ $bg }}; color: {{ $color }}; font-size: 6px; font-weight: 900; border-radius: 2px; margin-left: 1px;">{{ $i }}</span>
                                        @endfor
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        {{-- PROGRESS STYLE (DEFAULT) --}}
                        <div style="padding: 2px 4px;">
                            @foreach($affectiveTraits as $trait)
                                @php
                                    $sc = \App\Support\PsychomotorService::ratingToScore($trait['rating']);
                                    $pct = $sc * 20;
                                    $barColor = $sc >= 4 ? '#059669' : ($sc === 3 ? '#d97706' : '#dc2626');
                                @endphp
                                <div style="display: table; width: 100%; padding: 1.5px 0; border-bottom: 1px dotted #e2e8f0;">
                                    <div style="display: table-cell; font-size: 6.5px; font-weight: 700; color: {{ $rcLabelColor ?? '#374151' }}; vertical-align: middle;">{{ $trait['name'] }}</div>
                                    <div style="display: table-cell; text-align: right; vertical-align: middle; width: 62px;">
                                        <span style="font-size: 6px; font-weight: 700; color: #475569; margin-right: 2px;">{{ $trait['rating'] }}</span>
                                        <span style="display: inline-block; width: 30px; height: 4px; background: #e2e8f0; border-radius: 2px; overflow: hidden; vertical-align: middle;">
                                            <span style="display: block; height: 100%; width: {{ $pct }}%; background: {{ $barColor }}; border-radius: 2px;"></span>
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- COLUMN 2: PSYCHOMOTOR DOMAIN --}}
            <div style="display: table-cell; width: 49.5%; vertical-align: top; padding-left: 2px;">
                <div style="border: 1px solid {{ $rcBorderColor ?? '#e2e8f0' }}; border-radius: 3px; background: white; overflow: hidden;">
                    <div style="background: {{ $rcBorderColor ?? '#1e3a5f' }}; color: white; padding: 2.5px 4px; font-size: 6.5px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; text-align: center;">
                        Psychomotor &amp; Practical Skills
                    </div>

                    @if($style === 'grid')
                        <table style="width: 100%; border-collapse: collapse; font-size: 6px;">
                            <thead>
                                <tr style="background: #f1f5f9; color: #334155; border-bottom: 1px solid #e2e8f0;">
                                    <th style="text-align: left; padding: 2px 4px; font-weight: 800;">Skill</th>
                                    <th style="width: 10%; text-align: center; padding: 2px 1px; font-weight: 800;">5</th>
                                    <th style="width: 10%; text-align: center; padding: 2px 1px; font-weight: 800;">4</th>
                                    <th style="width: 10%; text-align: center; padding: 2px 1px; font-weight: 800;">3</th>
                                    <th style="width: 10%; text-align: center; padding: 2px 1px; font-weight: 800;">2</th>
                                    <th style="width: 10%; text-align: center; padding: 2px 1px; font-weight: 800;">1</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($psychomotorTraitsList as $trait)
                                    @php $sc = \App\Support\PsychomotorService::ratingToScore($trait['rating']); @endphp
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding: 1.5px 4px; font-weight: 700; color: {{ $rcLabelColor ?? '#374151' }};">{{ $trait['name'] }}</td>
                                        <td style="text-align: center; font-weight: 900; color: #059669; font-size: 7px;">@if($sc === 5)&check;@endif</td>
                                        <td style="text-align: center; font-weight: 900; color: #2563eb; font-size: 7px;">@if($sc === 4)&check;@endif</td>
                                        <td style="text-align: center; font-weight: 900; color: #d97706; font-size: 7px;">@if($sc === 3)&check;@endif</td>
                                        <td style="text-align: center; font-weight: 900; color: #ea580c; font-size: 7px;">@if($sc === 2)&check;@endif</td>
                                        <td style="text-align: center; font-weight: 900; color: #dc2626; font-size: 7px;">@if($sc === 1)&check;@endif</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @elseif($style === 'numeric')
                        <div style="padding: 2px 4px;">
                            @foreach($psychomotorTraitsList as $trait)
                                @php $sc = \App\Support\PsychomotorService::ratingToScore($trait['rating']); @endphp
                                <div style="display: table; width: 100%; padding: 1.5px 0; border-bottom: 1px dotted #e2e8f0;">
                                    <div style="display: table-cell; font-size: 6.5px; font-weight: 700; color: {{ $rcLabelColor ?? '#374151' }}; vertical-align: middle;">{{ $trait['name'] }}</div>
                                    <div style="display: table-cell; text-align: right; vertical-align: middle; width: 62px;">
                                        @for($i = 1; $i <= 5; $i++)
                                            @php
                                                $active = ($sc === $i);
                                                $bg = $active ? ($i === 5 ? '#059669' : ($i === 4 ? '#2563eb' : ($i === 3 ? '#d97706' : ($i === 2 ? '#ea580c' : '#dc2626')))) : '#f1f5f9';
                                                $color = $active ? '#ffffff' : '#94a3b8';
                                            @endphp
                                            <span style="display: inline-block; width: 9px; height: 9px; line-height: 9px; text-align: center; background: {{ $bg }}; color: {{ $color }}; font-size: 6px; font-weight: 900; border-radius: 2px; margin-left: 1px;">{{ $i }}</span>
                                        @endfor
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        {{-- PROGRESS STYLE (DEFAULT) --}}
                        <div style="padding: 2px 4px;">
                            @foreach($psychomotorTraitsList as $trait)
                                @php
                                    $sc = \App\Support\PsychomotorService::ratingToScore($trait['rating']);
                                    $pct = $sc * 20;
                                    $barColor = $sc >= 4 ? '#059669' : ($sc === 3 ? '#d97706' : '#dc2626');
                                @endphp
                                <div style="display: table; width: 100%; padding: 1.5px 0; border-bottom: 1px dotted #e2e8f0;">
                                    <div style="display: table-cell; font-size: 6.5px; font-weight: 700; color: {{ $rcLabelColor ?? '#374151' }}; vertical-align: middle;">{{ $trait['name'] }}</div>
                                    <div style="display: table-cell; text-align: right; vertical-align: middle; width: 62px;">
                                        <span style="font-size: 6px; font-weight: 700; color: #475569; margin-right: 2px;">{{ $trait['rating'] }}</span>
                                        <span style="display: inline-block; width: 30px; height: 4px; background: #e2e8f0; border-radius: 2px; overflow: hidden; vertical-align: middle;">
                                            <span style="display: block; height: 100%; width: {{ $pct }}%; background: {{ $barColor }}; border-radius: 2px;"></span>
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div style="font-size: 5.5px; text-align: right; color: #64748b; font-style: italic; margin-top: 2px; padding-right: 4px;">
        Rating Scale: 5 = Excellent &bull; 4 = Good &bull; 3 = Average &bull; 2 = Fair &bull; 1 = Poor
    </div>
</div>
@endif
