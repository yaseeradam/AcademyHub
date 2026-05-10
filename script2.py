import re

with open('resources/views/pages/students/show.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# For Profile Tab (lines ~577 to ~718 in the view_file logs)
# We want to replace the `space-y-6 xl:col-span-2` div which contains all the 3 cards.
# Wait, let's just do a string replacement for the cards. Let's write the whole profile tab again.

profile_regex = re.compile(r'<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">.*?<div class="xl:col-span-1">.*?</div>\s*</div>\s*</div>\s*@endsection', re.DOTALL)

profile_new = """<div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between px-6 py-5 border-b border-slate-100 bg-slate-50/50">
                    <div>
                        <div class="text-base font-bold text-slate-800">Student Profile Summary</div>
                        <div class="mt-0.5 text-xs text-slate-400">Personal details, enrolled subjects, and guardian information</div>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-x-10 gap-y-10 xl:grid-cols-3">
                        
                        {{-- 1. Personal Information --}}
                        <div class="xl:col-span-1 space-y-6">
                            <div>
                                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 mb-4">
                                    <svg class="h-4 w-4 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" /></svg>
                                    Personal Details
                                </h3>
                                <div class="space-y-4">
                                    @foreach ([
                                        'Admission Number' => $student->admission_number,
                                        'Gender'           => $student->gender,
                                        'Blood Group'      => $student->blood_group ?: '—',
                                        'Date of Birth'    => $student->dob?->format('M j, Y') ?: '—',
                                        'Class'            => $student->schoolClass?->name ?: '—',
                                        'Section'          => $student->section?->name ?: '—',
                                    ] as $label => $value)
                                        <div class="flex justify-between border-b border-slate-50 pb-2">
                                            <div class="text-sm font-medium text-slate-500">{{ $label }}</div>
                                            <div class="text-sm font-bold text-slate-800">{{ $value }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- 2. Guardian Information --}}
                        <div class="xl:col-span-1 space-y-6">
                            <div>
                                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 mb-4">
                                    <svg class="h-4 w-4 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M23 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /></svg>
                                    Guardian Information
                                </h3>
                                <div class="space-y-4">
                                    <div class="rounded-xl bg-orange-50/50 p-4 ring-1 ring-orange-100/50 text-center">
                                        <div class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-white text-orange-600 shadow-sm ring-1 ring-orange-100 mb-2">
                                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                        </div>
                                        <div class="text-sm font-bold text-slate-800">{{ $student->guardian_name ?: '—' }}</div>
                                        <div class="mt-1 text-xs font-semibold text-slate-500">{{ $student->guardian_phone ?: 'No phone provided' }}</div>
                                        <div class="mt-3 text-xs text-slate-400 border-t border-orange-100/50 pt-3">{{ $student->guardian_address ?: 'No address provided' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 3. Enrolled Subjects & Activity --}}
                        <div class="xl:col-span-1 space-y-6">
                            <div>
                                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 mb-4">
                                    <svg class="h-4 w-4 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" /><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5V4.5A2.5 2.5 0 0 1 6.5 2z" /></svg>
                                    Enrolled Subjects
                                </h3>
                                @if($student->schoolClass && $student->schoolClass->subjects->count() > 0)
                                    <div class="space-y-2">
                                        @foreach($student->schoolClass->subjects->take(4) as $subject)
                                            <div class="flex items-center gap-3 rounded-lg bg-slate-50 px-3 py-2">
                                                <div class="grid h-6 w-6 shrink-0 place-items-center rounded bg-white text-orange-500 shadow-sm ring-1 ring-slate-100">
                                                    <span class="text-[10px] font-bold">{{ substr($subject->name, 0, 1) }}</span>
                                                </div>
                                                <div class="truncate text-sm font-semibold text-slate-700">{{ $subject->name }}</div>
                                            </div>
                                        @endforeach
                                        @if($student->schoolClass->subjects->count() > 4)
                                            <div class="text-center text-xs font-semibold text-slate-500 py-1">
                                                +{{ $student->schoolClass->subjects->count() - 4 }} more
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="rounded-xl border border-dashed border-slate-200 p-4 text-center text-sm text-slate-500">
                                        No subjects assigned
                                    </div>
                                @endif
                            </div>
                            
                            <div class="pt-2">
                                <h3 class="flex items-center gap-2 text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 mb-4">
                                    <svg class="h-4 w-4 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 8v4l3 3" /><circle cx="12" cy="12" r="10" /></svg>
                                    Recent Activity
                                </h3>
                                <div class="space-y-4">
                                    @foreach ([
                                        ['title' => 'Profile viewed', 'time' => now()->diffForHumans(), 'icon' => 'rgb(249 115 22)'],
                                    ] as $item)
                                        <div class="flex items-center gap-3">
                                            <div class="h-2 w-2 rounded-full" style="background-color: {{ $item['icon'] }};"></div>
                                            <div class="flex-1 text-sm font-medium text-slate-700">{{ $item['title'] }}</div>
                                            <div class="text-xs text-slate-400">{{ $item['time'] }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
    </div>
@endsection"""

# Replace Profile tab content
content = profile_regex.sub(profile_new, content)

# Replace duplicate Title cards in Attendance / Analytics / Finance / Results
# Attendance
attn_old = """<div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="text-sm font-semibold text-slate-800">Attendance</div>
                            <div class="mt-1 text-sm text-slate-500">History for this student (latest 30 marks).</div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('attendance') }}" class="btn-primary">Open Attendance</a>
                        </div>
                    </div>
                </div>"""
attn_new = """"""

content = content.replace(attn_old, attn_new)

# Since we remove the attendance title card, we can put its content in the table card.
# The table currently is just wrapped in `<x-table>`. Let's wrap it in a proper big card like the index.
table_attn_old = """<x-table>"""
table_attn_new = """<div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden mt-6">
                <div class="flex flex-col gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:justify-between bg-slate-50/50">
                    <div>
                        <div class="text-base font-bold text-slate-800">Attendance History</div>
                        <div class="mt-0.5 text-xs text-slate-400">Latest 30 marks for this student.</div>
                    </div>
                    <div>
                        <a href="{{ route('attendance') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-50">Open Attendance</a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">"""

content = content.replace(table_attn_old, table_attn_new)
content = content.replace("</x-table>", "</table></div></div>")

# For Analytics
analytics_old = """<div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100">
                    <div class="text-sm font-semibold text-slate-800">Performance Analytics</div>
                    <div class="mt-1 text-sm text-slate-500">Comprehensive performance tracking and insights.</div>
                </div>"""
analytics_new = """<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between px-2 pt-2">
                    <div>
                        <div class="text-lg font-bold text-slate-800">Performance Analytics</div>
                        <div class="text-sm text-slate-500 mt-1">Comprehensive performance tracking and insights.</div>
                    </div>
                </div>"""

content = content.replace(analytics_old, analytics_new)


with open('resources/views/pages/students/show.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)
