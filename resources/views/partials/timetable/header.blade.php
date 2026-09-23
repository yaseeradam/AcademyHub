@php
    $schoolDisplayName = ($schoolName && $schoolName !== 'Your School Name' && $schoolName !== 'AcademyHub')
        ? $schoolName
        : 'ALIJABAH INTEGRATED ACADEMY ARGUNGU';

    $schoolTaglineText = ($schoolTagline && !str_contains($schoolTagline, "what's happening"))
        ? $schoolTagline
        : 'Learning Today | Leading Tomorrow';

    $currentClassName = $className ?? 'Nursery Class';

    // Bouncy rainbow letter colors
    $rainbowColors = ['#0284c7', '#f97316', '#16a34a', '#eab308', '#8b5cf6', '#06b6d4', '#ec4899', '#3b82f6', '#10b981', '#f43f5e'];
    $chars = mb_str_split(strtoupper($currentClassName));
@endphp

<div class="timetable-poster-header relative w-full select-none overflow-hidden rounded-t-3xl bg-gradient-to-b from-[#e0f4ff]/70 via-[#f4fbff]/90 to-white px-4 pt-4 pb-3 sm:px-8 sm:pt-6 sm:pb-4 border-b border-sky-100/80">
    <div class="flex flex-col lg:flex-row items-center justify-between gap-4">
        
        {{-- Left: Logo and Mascot Kids --}}
        <div class="flex items-center gap-3 sm:gap-4 shrink-0">
            {{-- School Crest Emblem --}}
            <div class="relative flex items-center justify-center">
                @if(!empty($logoBase64) || !empty($logoUrl))
                    <div class="h-20 w-20 sm:h-24 sm:w-24 rounded-full p-1 bg-white shadow-md border-2 border-[#163c55]/30 flex items-center justify-center overflow-hidden">
                        <img src="{{ $logoBase64 ?? $logoUrl }}" alt="Logo" class="max-h-full max-w-full object-contain">
                    </div>
                @else
                    {{-- Illustrated Alijabah Integrated Academy Crest --}}
                    <svg class="h-20 w-20 sm:h-24 sm:w-24 drop-shadow-md" viewBox="0 0 140 140" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Outer decorative ring -->
                        <circle cx="70" cy="70" r="68" fill="#163c55" stroke="#0ea5e9" stroke-width="2"/>
                        <circle cx="70" cy="70" r="58" fill="#ffffff" stroke="#e2e8f0" stroke-width="1.5"/>
                        <circle cx="70" cy="70" r="54" fill="#f8fafc" stroke="#163c55" stroke-dasharray="2 2" stroke-width="1"/>
                        
                        <!-- Circular text top & bottom -->
                        <path id="crestTopArc" d="M 18,70 A 52,52 0 1,1 122,70" fill="none" />
                        <path id="crestBottomArc" d="M 122,70 A 52,52 0 0,1 18,70" fill="none" />
                        <text font-size="7.2" font-family="'Fredoka', 'Nunito', sans-serif" font-weight="800" fill="#ffffff" letter-spacing="1.2">
                            <textPath href="#crestTopArc" startOffset="50%" text-anchor="middle">
                                ALIJABAH INTEGRATED ACADEMY
                            </textPath>
                        </text>
                        <text font-size="6.2" font-family="'Fredoka', 'Nunito', sans-serif" font-weight="700" fill="#bae6fd" letter-spacing="0.8">
                            <textPath href="#crestBottomArc" startOffset="50%" text-anchor="middle">
                                ★ LEARNING TODAY LEADING TOMORROW ★
                            </textPath>
                        </text>
                        
                        <!-- Open Book illustration -->
                        <path d="M 46 64 Q 70 56 70 76 Q 70 56 94 64 L 94 88 Q 70 80 70 100 Q 70 80 46 88 Z" fill="#ffffff" stroke="#163c55" stroke-width="2.5" stroke-linejoin="round"/>
                        <path d="M 70 76 L 70 100" stroke="#163c55" stroke-width="2"/>
                        <!-- Book lines -->
                        <line x1="52" y1="71" x2="65" y2="69" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round"/>
                        <line x1="52" y1="77" x2="65" y2="75" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round"/>
                        <line x1="75" y1="69" x2="88" y2="71" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round"/>
                        <line x1="75" y1="75" x2="88" y2="77" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round"/>
                        
                        <!-- Inkpot & Quill -->
                        <ellipse cx="70" cy="102" rx="14" ry="6" fill="#0284c7" stroke="#163c55" stroke-width="1.5"/>
                        <path d="M 64 98 L 76 98 L 78 103 L 62 103 Z" fill="#0369a1"/>
                        <!-- Feather quill -->
                        <path d="M 70 98 Q 76 80 84 62 Q 81 72 73 95" fill="#38bdf8" stroke="#163c55" stroke-width="1.5"/>
                    </svg>
                @endif
            </div>

            {{-- Illustrated School Kids (Boy Waving & Girl with Hijab) --}}
            <div class="hidden sm:block">
                <svg class="h-20 w-32 sm:h-24 sm:w-36 drop-shadow-sm" viewBox="0 0 160 110" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- BOY (Left) -->
                    <g transform="translate(10, 5)">
                        <!-- Backpack -->
                        <rect x="8" y="44" width="16" height="28" rx="6" fill="#0284c7" stroke="#163c55" stroke-width="2"/>
                        
                        <!-- Body / Shirt -->
                        <path d="M 18 52 L 48 52 L 52 85 L 14 85 Z" fill="#ffffff" stroke="#163c55" stroke-width="2.5" stroke-linejoin="round"/>
                        <!-- Navy Collar -->
                        <path d="M 24 52 L 33 64 L 42 52 Z" fill="#163c55"/>
                        <circle cx="33" cy="72" r="1.5" fill="#163c55"/>
                        <circle cx="33" cy="78" r="1.5" fill="#163c55"/>
                        <!-- Backpack strap -->
                        <path d="M 22 52 L 20 74" stroke="#0284c7" stroke-width="3" stroke-linecap="round"/>

                        <!-- Waving Arm (Left side of boy) -->
                        <path d="M 18 55 Q 6 42 2 30" stroke="#a36843" stroke-width="7" stroke-linecap="round"/>
                        <!-- Waving Hand -->
                        <circle cx="2" cy="27" r="5" fill="#a36843"/>
                        <path d="M -1 25 Q -4 20 -2 18" stroke="#a36843" stroke-width="2.5" stroke-linecap="round"/>
                        <!-- Motion lines for waving -->
                        <path d="M -7 22 Q -9 27 -6 32" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" fill="none"/>
                        <path d="M -11 25 Q -13 28 -10 32" stroke="#f59e0b" stroke-width="1.5" stroke-linecap="round" fill="none"/>

                        <!-- Right Arm -->
                        <path d="M 48 55 Q 54 66 50 78" stroke="#a36843" stroke-width="6.5" stroke-linecap="round"/>
                        <circle cx="50" cy="80" r="4.5" fill="#a36843"/>

                        <!-- Head -->
                        <ellipse cx="33" cy="35" rx="14" ry="15" fill="#a36843"/>
                        <!-- Ears -->
                        <circle cx="18" cy="35" r="3.5" fill="#a36843"/>
                        <circle cx="48" cy="35" r="3.5" fill="#a36843"/>
                        
                        <!-- Curly Hair -->
                        <path d="M 18 32 C 16 22 22 15 33 15 C 44 15 50 22 48 32 C 45 23 40 20 33 20 C 26 20 21 23 18 32 Z" fill="#261912"/>
                        <circle cx="24" cy="18" r="4" fill="#261912"/>
                        <circle cx="33" cy="16" r="4.5" fill="#261912"/>
                        <circle cx="42" cy="18" r="4" fill="#261912"/>
                        
                        <!-- Face Eyes & Smile -->
                        <ellipse cx="28" cy="34" rx="2" ry="2.8" fill="#1e293b"/>
                        <circle cx="27.2" cy="33" r="0.8" fill="#ffffff"/>
                        <ellipse cx="38" cy="34" rx="2" ry="2.8" fill="#1e293b"/>
                        <circle cx="37.2" cy="33" r="0.8" fill="#ffffff"/>
                        <!-- Rosy cheeks -->
                        <circle cx="24" cy="39" r="2.5" fill="#f87171" opacity="0.5"/>
                        <circle cx="42" cy="39" r="2.5" fill="#f87171" opacity="0.5"/>
                        <!-- Happy Smile -->
                        <path d="M 28 40 Q 33 46 38 40" stroke="#1e293b" stroke-width="1.8" stroke-linecap="round" fill="#e11d48"/>
                    </g>

                    <!-- GIRL WITH HIJAB (Right) -->
                    <g transform="translate(75, 8)">
                        <!-- Backpack -->
                        <rect x="38" y="44" width="14" height="26" rx="5" fill="#ec4899" stroke="#163c55" stroke-width="2"/>

                        <!-- Uniform Body -->
                        <path d="M 16 56 L 46 56 L 50 85 L 12 85 Z" fill="#0284c7" stroke="#163c55" stroke-width="2.5" stroke-linejoin="round"/>
                        <!-- White Hijab Draped over shoulders -->
                        <path d="M 12 48 Q 31 68 50 48 L 46 80 Q 31 84 16 80 Z" fill="#ffffff" stroke="#163c55" stroke-width="2"/>

                        <!-- Arms -->
                        <path d="M 14 58 Q 8 68 12 78" stroke="#9e623f" stroke-width="6" stroke-linecap="round"/>
                        <path d="M 48 58 Q 54 68 50 78" stroke="#9e623f" stroke-width="6" stroke-linecap="round"/>

                        <!-- Hijab Head Covering Outer -->
                        <ellipse cx="31" cy="32" rx="19" ry="22" fill="#ffffff" stroke="#163c55" stroke-width="2.5"/>
                        <!-- Face Opening -->
                        <ellipse cx="31" cy="34" rx="11" ry="12.5" fill="#9e623f"/>
                        
                        <!-- Hijab fold accent line -->
                        <path d="M 20 28 Q 31 22 42 28" stroke="#cbd5e1" stroke-width="1.5" fill="none"/>
                        
                        <!-- Face Eyes & Cheerful Smile -->
                        <ellipse cx="27" cy="33" rx="1.8" ry="2.6" fill="#1e293b"/>
                        <circle cx="26.3" cy="32" r="0.7" fill="#ffffff"/>
                        <ellipse cx="35" cy="33" rx="1.8" ry="2.6" fill="#1e293b"/>
                        <circle cx="34.3" cy="32" r="0.7" fill="#ffffff"/>
                        <!-- Rosy cheeks -->
                        <circle cx="23" cy="38" r="2.2" fill="#fb7185" opacity="0.6"/>
                        <circle cx="39" cy="38" r="2.2" fill="#fb7185" opacity="0.6"/>
                        <!-- Sweet smile -->
                        <path d="M 27 39 Q 31 44 35 39" stroke="#1e293b" stroke-width="1.8" stroke-linecap="round" fill="#e11d48"/>
                    </g>
                </svg>
            </div>
        </div>

        {{-- Center: School Name, Motto, Bubbly Class Badge & Ribbon --}}
        <div class="flex flex-col items-center text-center max-w-2xl px-2">
            
            {{-- School Title --}}
            <h1 class="text-xl sm:text-2xl md:text-3xl font-black text-[#163c55] tracking-tight uppercase leading-tight font-display drop-shadow-xs">
                {{ $schoolDisplayName }}
            </h1>

            {{-- Motto / Tagline with Golden Wing Lines --}}
            <div class="mt-1 flex items-center justify-center gap-2 sm:gap-3 text-xs sm:text-sm font-bold text-[#1e40af] italic tracking-wide">
                <span class="h-0.5 w-6 sm:w-10 bg-amber-400 rounded-full"></span>
                <span>{{ $schoolTaglineText }}</span>
                <span class="h-0.5 w-6 sm:w-10 bg-amber-400 rounded-full"></span>
            </div>

            {{-- Playful Bubbly Class Badge with Sparkles --}}
            <div class="relative mt-3 sm:mt-4 inline-flex items-center justify-center">
                <!-- Sparkle Doodles Left & Right -->
                <span class="absolute -left-6 sm:-left-8 top-1 text-amber-400 text-lg sm:text-xl animate-pulse">✦</span>
                <span class="absolute -left-3 sm:-left-4 -bottom-1 text-sky-400 text-sm sm:text-base">★</span>
                <span class="absolute -right-6 sm:-right-8 top-1 text-amber-400 text-lg sm:text-xl animate-pulse">✦</span>
                <span class="absolute -right-3 sm:-right-4 -bottom-1 text-pink-400 text-sm sm:text-base">★</span>

                <!-- Bubbly Rounded Cloud Pill -->
                <div class="rounded-full bg-white px-5 py-1.5 sm:px-7 sm:py-2 border-3 border-[#163c55] shadow-[0_4px_12px_rgba(22,60,85,0.12)] flex items-center gap-1.5">
                    <span class="text-lg sm:text-2xl md:text-3xl font-black tracking-wider flex items-center font-display">
                        @foreach($chars as $i => $char)
                            @if($char === ' ')
                                <span class="w-2 sm:w-3"></span>
                            @else
                                <span style="color: {{ $rainbowColors[$i % count($rainbowColors)] }};" class="inline-block transform hover:-translate-y-0.5 transition-transform">
                                    {{ $char }}
                                </span>
                            @endif
                        @endforeach
                    </span>
                </div>
            </div>

            {{-- 3D Golden Ribbon Banner for "TIMETABLE" --}}
            <div class="relative -mt-2 sm:-mt-2.5 z-10 flex items-center justify-center">
                <div class="relative bg-gradient-to-r from-amber-400 via-yellow-400 to-amber-400 text-[#163c55] font-black px-6 py-1 sm:px-9 sm:py-1 rounded-sm shadow-md border-y-2 border-[#163c55] uppercase tracking-[0.25em] text-xs sm:text-sm md:text-base font-display">
                    TIMETABLE
                    <!-- Left ribbon tail fold -->
                    <div class="absolute -left-3 top-1/2 -translate-y-1/2 w-0 h-0 border-t-[10px] border-t-transparent border-b-[10px] border-b-transparent border-r-[12px] border-r-amber-500 -z-10"></div>
                    <!-- Right ribbon tail fold -->
                    <div class="absolute -right-3 top-1/2 -translate-y-1/2 w-0 h-0 border-t-[10px] border-t-transparent border-b-[10px] border-b-transparent border-l-[12px] border-l-amber-500 -z-10"></div>
                </div>
            </div>

        </div>

        {{-- Right: Smiling Sun, Quote, Books & Pencils --}}
        <div class="hidden md:flex items-center gap-3 sm:gap-4 shrink-0">
            <svg class="h-24 w-36 sm:h-28 sm:w-44" viewBox="0 0 180 110" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Smiling Sun with rays -->
                <g transform="translate(130, 26)">
                    <!-- Sun rays -->
                    <g stroke="#f59e0b" stroke-width="2.5" stroke-linecap="round">
                        <line x1="0" y1="-22" x2="0" y2="-27"/>
                        <line x1="16" y1="-16" x2="20" y2="-20"/>
                        <line x1="22" y1="0" x2="27" y2="0"/>
                        <line x1="16" y1="16" x2="20" y2="20"/>
                        <line x1="0" y1="22" x2="0" y2="27"/>
                        <line x1="-16" y1="16" x2="-20" y2="20"/>
                        <line x1="-22" y1="0" x2="-27" y2="0"/>
                        <line x1="-16" y1="-16" x2="-20" y2="-20"/>
                    </g>
                    <!-- Sun circle face -->
                    <circle cx="0" cy="0" r="17" fill="#facc15" stroke="#f59e0b" stroke-width="2"/>
                    <!-- Eyes -->
                    <circle cx="-5" cy="-3" r="2" fill="#1e293b"/>
                    <circle cx="-4" cy="-4" r="0.7" fill="#ffffff"/>
                    <circle cx="5" cy="-3" r="2" fill="#1e293b"/>
                    <circle cx="6" cy="-4" r="0.7" fill="#ffffff"/>
                    <!-- Rosy Cheeks -->
                    <circle cx="-8" cy="2" r="2.5" fill="#fb7185" opacity="0.6"/>
                    <circle cx="8" cy="2" r="2.5" fill="#fb7185" opacity="0.6"/>
                    <!-- Happy Smile -->
                    <path d="M -5 3 Q 0 8 5 3" stroke="#1e293b" stroke-width="1.8" stroke-linecap="round" fill="none"/>
                </g>

                <!-- "Small Steps Big Dreams" Playful Text -->
                <g transform="translate(10, 20)">
                    <text x="35" y="16" font-family="'Fredoka', 'Nunito', sans-serif" font-weight="900" font-size="14" fill="#163c55" transform="rotate(-6 35 16)">
                        Small
                    </text>
                    <text x="38" y="32" font-family="'Fredoka', 'Nunito', sans-serif" font-weight="900" font-size="14" fill="#163c55" transform="rotate(-6 38 32)">
                        Steps
                    </text>
                    <text x="42" y="52" font-family="'Fredoka', 'Nunito', sans-serif" font-weight="900" font-size="18" fill="#163c55" transform="rotate(-6 42 52)">
                        BIG
                    </text>
                    <text x="25" y="74" font-family="'Fredoka', 'Nunito', sans-serif" font-weight="900" font-size="20" fill="#163c55" transform="rotate(-6 25 74)">
                        Dreams
                    </text>
                </g>

                <!-- Books & Colored Pencils in Cup -->
                <g transform="translate(115, 60)">
                    <!-- Stack of 3 Books -->
                    <!-- Bottom Green Book -->
                    <rect x="0" y="24" width="46" height="10" rx="2" fill="#22c55e" stroke="#163c55" stroke-width="1.8"/>
                    <line x1="6" y1="26" x2="42" y2="26" stroke="#ffffff" stroke-width="1.2" opacity="0.8"/>
                    <!-- Middle Blue Book -->
                    <rect x="2" y="14" width="44" height="10" rx="2" fill="#0284c7" stroke="#163c55" stroke-width="1.8"/>
                    <line x1="8" y1="16" x2="40" y2="16" stroke="#ffffff" stroke-width="1.2" opacity="0.8"/>
                    <!-- Top Red/Orange Book -->
                    <rect x="4" y="4" width="42" height="10" rx="2" fill="#f97316" stroke="#163c55" stroke-width="1.8"/>
                    <line x1="10" y1="6" x2="38" y2="6" stroke="#ffffff" stroke-width="1.2" opacity="0.8"/>

                    <!-- Cup with Pencils on top right -->
                    <g transform="translate(24, -20)">
                        <!-- Cup -->
                        <path d="M 4 12 L 18 12 L 16 26 L 6 26 Z" fill="#ffffff" stroke="#163c55" stroke-width="1.5"/>
                        <!-- Pencils -->
                        <line x1="8" y1="12" x2="5" y2="0" stroke="#ef4444" stroke-width="2.5" stroke-linecap="round"/>
                        <line x1="11" y1="12" x2="11" y2="-3" stroke="#eab308" stroke-width="2.5" stroke-linecap="round"/>
                        <line x1="14" y1="12" x2="17" y2="0" stroke="#10b981" stroke-width="2.5" stroke-linecap="round"/>
                    </g>
                </g>
            </svg>
        </div>

    </div>
</div>
