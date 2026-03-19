{{-- Psychomotor Domain Partial --}}
{{-- Styles should be included in the parent template --}}
@if($showPsychomotor ?? false)
<div class="psychomotor-box" style="border: 2px solid {{ $rcBorderColor ?? '#ccc' }}; border-radius: 8px; padding: 10px; margin-bottom: 14px; background: {{ $rcBgLight ?? '#f9fafb' }};">
    <div style="font-size: 9px; font-weight: 800; color: {{ $rcTitleColor ?? '#374151' }}; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; text-align: center;">
        Psychomotor Domain / Affective Skills
    </div>
    <div style="display: table; width: 100%;">
        @php
            $psychomotorTraits = [
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
            $chunked = array_chunk($psychomotorTraits, 2);
        @endphp
        @foreach($chunked as $pair)
        <div style="display: table-row;">
            @foreach($pair as $trait)
            <div style="display: table-cell; width: 50%; padding: 3px 6px;">
                <div style="background: white; border: 1px solid #e5e7eb; border-radius: 6px; padding: 6px 10px;">
                    <span style="font-size: 8px; color: {{ $rcLabelColor ?? '#374151' }}; font-weight: 700; display: inline-block; width: 55%;">{{ $trait['name'] }}</span>
                    <span style="font-size: 8px; font-weight: 600; color: #6b7280; display: inline-block; width: 40%; text-align: right;">
                        {{ $trait['rating'] }}
                        <span style="display: inline-block; width: 40px; height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden; vertical-align: middle; margin-left: 4px;">
                            <span style="display: block; height: 100%; border-radius: 3px;
                                @if($trait['rating'] === 'Excellent') background: #059669; width: 100%;
                                @elseif($trait['rating'] === 'Good') background: #2563eb; width: 75%;
                                @elseif($trait['rating'] === 'Average') background: #d97706; width: 50%;
                                @else background: #ea580c; width: 25%;
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
</div>
@endif
