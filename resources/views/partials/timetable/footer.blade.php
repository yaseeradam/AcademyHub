<div class="timetable-poster-footer relative w-full select-none overflow-hidden rounded-b-3xl bg-gradient-to-t from-[#e0f4ff]/80 via-[#f4fbff]/90 to-white px-4 py-3 sm:px-8 sm:py-4 border-t border-sky-100/80">
    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
        
        {{-- Left: Stack of Books (Learn • Grow • Succeed) and Pencils --}}
        <div class="flex items-center gap-3">
            <svg class="h-16 w-36 sm:h-18 sm:w-44" viewBox="0 0 170 70" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Stack of 3 Books with Titles -->
                <!-- Bottom Orange Book: Succeed -->
                <rect x="2" y="44" width="96" height="18" rx="4" fill="#f97316" stroke="#163c55" stroke-width="2"/>
                <rect x="94" y="46" width="3" height="14" fill="#fed7aa"/>
                <text x="50" y="57" font-family="'Fredoka', 'Nunito', sans-serif" font-weight="900" font-size="11" fill="#ffffff" text-anchor="middle" letter-spacing="0.5">
                    Succeed
                </text>

                <!-- Middle Green Book: Grow -->
                <rect x="8" y="24" width="90" height="18" rx="4" fill="#22c55e" stroke="#163c55" stroke-width="2"/>
                <rect x="94" y="26" width="3" height="14" fill="#bbf7d0"/>
                <text x="53" y="37" font-family="'Fredoka', 'Nunito', sans-serif" font-weight="900" font-size="11" fill="#ffffff" text-anchor="middle" letter-spacing="0.5">
                    Grow
                </text>

                <!-- Top Blue Book: Learn -->
                <rect x="14" y="4" width="84" height="18" rx="4" fill="#0284c7" stroke="#163c55" stroke-width="2"/>
                <rect x="94" y="6" width="3" height="14" fill="#bae6fd"/>
                <text x="56" y="17" font-family="'Fredoka', 'Nunito', sans-serif" font-weight="900" font-size="11" fill="#ffffff" text-anchor="middle" letter-spacing="0.5">
                    Learn
                </text>

                <!-- Colored Pencils Cup beside books -->
                <g transform="translate(108, 12)">
                    <path d="M 6 22 L 24 22 L 21 48 L 9 48 Z" fill="#ffffff" stroke="#163c55" stroke-width="2"/>
                    <!-- Pencil 1 (Pink) -->
                    <line x1="10" y1="22" x2="6" y2="4" stroke="#ec4899" stroke-width="3.5" stroke-linecap="round"/>
                    <polygon points="6,4 4,1 8,2" fill="#fbcfe8"/>
                    <!-- Pencil 2 (Yellow) -->
                    <line x1="15" y1="22" x2="15" y2="0" stroke="#eab308" stroke-width="3.5" stroke-linecap="round"/>
                    <polygon points="15,0 13,-3 17,-3" fill="#fef08a"/>
                    <!-- Pencil 3 (Green) -->
                    <line x1="20" y1="22" x2="24" y2="4" stroke="#10b981" stroke-width="3.5" stroke-linecap="round"/>
                    <polygon points="24,4 27,2 24,0" fill="#a7f3d0"/>
                </g>

                <!-- Sparkle stars -->
                <text x="148" y="24" font-size="16" fill="#facc15">✦</text>
                <text x="156" y="46" font-size="12" fill="#38bdf8">★</text>
            </svg>
        </div>

        {{-- Center: Dark Navy Curved Banner (Knowledge — Character — Excellence) --}}
        <div class="flex items-center justify-center">
            <div class="rounded-full bg-[#163c55] px-6 py-2.5 sm:px-10 sm:py-3 shadow-lg border-2 border-[#0ea5e9]/40 flex items-center justify-center gap-3 sm:gap-6 text-white text-xs sm:text-sm font-black tracking-wide font-display">
                
                {{-- Knowledge --}}
                <div class="flex items-center gap-1.5 text-amber-300">
                    <svg class="h-4 w-4 sm:h-5 sm:w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2a7 7 0 0 0-7 7c0 2.38 1.19 4.47 3 5.74V17a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-2.26c1.81-1.27 3-3.36 3-5.74a7 7 0 0 0-7-7M9 21a1 1 0 0 0 1 1h4a1 1 0 0 0 1-1v-1H9v1z"/>
                    </svg>
                    <span class="text-white">Knowledge</span>
                </div>

                <span class="text-amber-400 font-bold">—</span>

                {{-- Character --}}
                <div class="flex items-center gap-1.5 text-amber-300">
                    <svg class="h-4 w-4 sm:h-5 sm:w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="m12 17.27 4.15 2.51c.76.46 1.69-.22 1.49-1.08l-1.1-4.72 3.67-3.18c.67-.58.31-1.68-.57-1.75l-4.83-.41-1.89-4.46c-.34-.81-1.5-.81-1.84 0L9.19 8.63l-4.83.41c-.88.07-1.24 1.17-.57 1.75l3.67 3.18-1.1 4.72c-.2.86.73 1.54 1.49 1.08l4.15-2.5z"/>
                    </svg>
                    <span class="text-white">Character</span>
                </div>

                <span class="text-amber-400 font-bold">—</span>

                {{-- Excellence --}}
                <div class="flex items-center gap-1.5 text-sky-300">
                    <svg class="h-4 w-4 sm:h-5 sm:w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                    </svg>
                    <span class="text-white">Excellence</span>
                </div>

            </div>
        </div>

        {{-- Right: Globe & Cute Potted Plant --}}
        <div class="flex items-center gap-3">
            <svg class="h-16 w-32 sm:h-18 sm:w-36" viewBox="0 0 130 70" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Desk Globe -->
                <g transform="translate(10, 4)">
                    <!-- Stand Arc -->
                    <path d="M 12 16 A 20 20 0 1 0 44 42" stroke="#163c55" stroke-width="3" stroke-linecap="round" fill="none"/>
                    <!-- Stand Base -->
                    <path d="M 28 46 L 28 58 M 18 58 L 38 58" stroke="#163c55" stroke-width="3" stroke-linecap="round"/>
                    <!-- Globe Sphere -->
                    <circle cx="28" cy="28" r="16" fill="#38bdf8" stroke="#163c55" stroke-width="2"/>
                    <!-- Green Continents -->
                    <path d="M 20 22 Q 26 16 32 20 Q 30 28 22 28 Z" fill="#22c55e"/>
                    <path d="M 22 34 Q 30 32 36 36 Q 32 42 24 40 Z" fill="#22c55e"/>
                </g>

                <!-- Potted Plant -->
                <g transform="translate(75, 14)">
                    <!-- Pot -->
                    <path d="M 10 32 L 26 32 L 23 50 L 13 50 Z" fill="#f97316" stroke="#163c55" stroke-width="2"/>
                    <rect x="8" y="28" width="20" height="4" rx="2" fill="#ea580c" stroke="#163c55" stroke-width="1.5"/>
                    <!-- Green Plant Leaves -->
                    <path d="M 18 28 Q 10 14 18 2 Q 26 14 18 28 Z" fill="#16a34a" stroke="#163c55" stroke-width="1.5"/>
                    <path d="M 18 28 Q 6 22 4 10 Q 14 14 18 28 Z" fill="#22c55e" stroke="#163c55" stroke-width="1.5"/>
                    <path d="M 18 28 Q 30 22 32 10 Q 22 14 18 28 Z" fill="#22c55e" stroke="#163c55" stroke-width="1.5"/>
                </g>

                <!-- Little Stars -->
                <text x="115" y="22" font-size="14" fill="#facc15">✦</text>
                <text x="65" y="18" font-size="12" fill="#38bdf8">★</text>
            </svg>
        </div>

    </div>
</div>
