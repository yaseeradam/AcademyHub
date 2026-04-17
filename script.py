import re

with open('resources/views/pages/students/show.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Replace Hero Section
hero_html = """
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-500/10 to-purple-500/10 shadow-sm ring-1 ring-slate-100">
        <div class="absolute inset-0 bg-white/80 backdrop-blur-3xl"></div>
        <div class="absolute right-0 top-0 bottom-0 w-48 opacity-10">
            <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full text-indigo-500">
                <circle cx="160" cy="100" r="130" stroke="currentColor" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="90" stroke="currentColor" stroke-width="0.5"/>
                <circle cx="160" cy="100" r="50" stroke="currentColor" stroke-width="0.5"/>
            </svg>
        </div>

        <div class="relative px-8 py-8 md:flex md:items-center md:justify-between">
            <div class="flex flex-col md:flex-row md:items-center gap-6">
                <div class="relative">
                    @if ($student->passport_photo_url)
                        <img
                            src="{{ $student->passport_photo_url }}"
                            alt="{{ $student->full_name }}"
                            class="h-28 w-28 rounded-full object-cover ring-4 ring-white shadow-xl"
                        />
                    @else
                        <div class="grid h-28 w-28 place-items-center rounded-full bg-gradient-to-br from-orange-400 to-amber-500 text-4xl font-black text-white ring-4 ring-white shadow-xl">
                            {{ $initials }}
                        </div>
                    @endif
                    <div class="absolute -bottom-2 -right-2 rounded-full border-4 border-white bg-green-500 px-2 py-0.5 text-xs font-bold text-white shadow-sm">
                        {{ $student->status }}
                    </div>
                </div>
                <div>
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight">{{ $student->full_name }}</h2>
                    <p class="mt-1 text-base font-semibold text-slate-500">{{ $studentMeta }}</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('students.admission-form', $student) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-orange-50 px-3 py-1.5 text-xs font-bold text-orange-600 transition hover:bg-orange-100">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                            Admission Form
                        </a>
                        @if (auth()->user()?->role === 'admin')
                            <a href="{{ route('students.edit', $student) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-600 transition hover:bg-slate-100 ring-1 ring-inset ring-slate-200">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                Edit Profile
                            </a>
                            <form
                                method="POST"
                                action="{{ route('students.destroy', $student) }}"
                                class="inline"
                                onsubmit="return confirm('Delete this student? This action cannot be undone.')"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-3 py-1.5 text-xs font-bold text-red-600 transition hover:bg-red-100">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"></path><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                    Delete
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            <div class="mt-6 md:mt-0">
                <a href="{{ route('students.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-50">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    Back to List
                </a>
            </div>
        </div>
    </div>
"""

# Replace x-page-header
header_pattern = re.compile(r'<x-page-header.*?</x-page-header>', re.DOTALL)
content = header_pattern.sub(hero_html, content)

# 2. Replace Tabs Card
tabs_old = """        <div class="card">
            <div class="px-6">
                <div class="flex flex-wrap gap-6 border-b border-gray-200/70">
                    @foreach ($tabs as $key => $label)
                        <a
                            href="{{ route('students.show', ['student' => $student, 'tab' => $key]) }}"
                            class="{{ $tab === $key ? 'border-b-2 border-brand-500 text-brand-700' : 'border-b-2 border-transparent text-slate-600 hover:text-slate-900' }} -mb-px py-4 text-sm font-semibold"
                        >
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>"""

tabs_new = """        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">
            <div class="flex overflow-x-auto">
                @foreach ($tabs as $key => $label)
                    <a
                        href="{{ route('students.show', ['student' => $student, 'tab' => $key]) }}"
                        class="{{ $tab === $key ? 'border-b-2 border-orange-500 text-orange-600 bg-orange-50/50' : 'border-b-2 border-transparent text-slate-500 hover:text-slate-800 hover:bg-slate-50/50' }} flex-1 min-w-[120px] text-center py-4 text-sm font-bold transition whitespace-nowrap"
                    >
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>"""

content = content.replace(tabs_old, tabs_new)

# 3. Replace Profile Tab Content completely
profile_old_pattern = re.compile(r'<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">.*?(?=@endif\s*</div>\s*@endsection)', re.DOTALL)

profile_new = """<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <div class="space-y-6 xl:col-span-2">
                    
                    <!-- Student Information Card -->
                    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="border-b border-slate-100 px-6 py-5">
                            <div class="flex items-center gap-3">
                                <div class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-orange-400 to-amber-500 text-white shadow-md">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                        <circle cx="9" cy="7" r="4" />
                                        <path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" />
                                    </svg>
                                </div>
                                <div class="text-base font-bold text-slate-800">Student Information</div>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                @foreach ([
                                    'Admission Number' => $student->admission_number,
                                    'Gender' => $student->gender,
                                    'Blood Group' => $student->blood_group ?: '—',
                                    'Date of Birth' => $student->dob?->format('F j, Y') ?: '—',
                                    'Class' => $student->schoolClass?->name ?: '—',
                                    'Section' => $student->section?->name ?: '—',
                                ] as $label => $value)
                                    <div>
                                        <div class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ $label }}</div>
                                        <div class="mt-1 text-base font-semibold text-slate-800">{{ $value }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Enrolled Subjects Card -->
                    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="border-b border-slate-100 px-6 py-5">
                            <div class="flex items-center gap-3">
                                <div class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 text-white shadow-md">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5V4.5A2.5 2.5 0 0 1 6.5 2z" />
                                    </svg>
                                </div>
                                <div class="text-base font-bold text-slate-800">Enrolled Subjects</div>
                            </div>
                        </div>
                        <div class="p-6">
                            @if($student->schoolClass && $student->schoolClass->subjects->count() > 0)
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    @foreach($student->schoolClass->subjects as $subject)
                                        <div class="flex items-center gap-3 rounded-xl bg-slate-50 p-3 ring-1 ring-slate-100">
                                            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-white text-violet-600 shadow-sm ring-1 ring-slate-200">
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5V4.5A2.5 2.5 0 0 1 6.5 2z" />
                                                </svg>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="truncate text-sm font-bold text-slate-800">{{ $subject->name }}</div>
                                                <div class="truncate text-xs font-medium text-slate-400">{{ $subject->code }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="py-8 text-center text-sm font-medium text-slate-500">
                                    No subjects assigned to this class yet.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Guardian Information Card -->
                    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="border-b border-slate-100 px-6 py-5">
                            <div class="flex items-center gap-3">
                                <div class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-cyan-400 to-teal-500 text-white shadow-md">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                        <circle cx="9" cy="7" r="4" />
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                    </svg>
                                </div>
                                <div class="text-base font-bold text-slate-800">Guardian Information</div>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Name</div>
                                    <div class="mt-1 text-base font-semibold text-slate-800">{{ $student->guardian_name ?: '—' }}</div>
                                </div>
                                <div>
                                    <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Phone</div>
                                    <div class="mt-1 text-base font-semibold text-slate-800">{{ $student->guardian_phone ?: '—' }}</div>
                                </div>
                                <div class="sm:col-span-2">
                                    <div class="text-xs font-bold uppercase tracking-wider text-slate-400">Address</div>
                                    <div class="mt-1 text-base font-semibold text-slate-800">{{ $student->guardian_address ?: '—' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-1">
                    <div class="sticky top-6 rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="border-b border-slate-100 px-6 py-5">
                            <div class="flex items-center gap-3">
                                <div class="grid h-10 w-10 place-items-center rounded-xl bg-slate-100 text-slate-500 shadow-[inset_0_1px_1px_rgba(255,255,255,0.7)] ring-1 ring-slate-200">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path d="M12 8v4l3 3" />
                                        <circle cx="12" cy="12" r="10" />
                                    </svg>
                                </div>
                                <div class="text-base font-bold text-slate-800">Recent Activities</div>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="space-y-6">
                                @foreach ([
                                    ['title' => 'Student record viewed', 'time' => now()->format('M j, Y g:i A'), 'icon' => 'rgb(249 115 22)', 'bg' => 'bg-orange-500'],
                                ] as $item)
                                    <div class="relative pl-5">
                                        <div class="absolute left-0 top-1.5 h-full w-px bg-slate-200"></div>
                                        <div class="absolute left-[-4px] top-1.5 h-2.5 w-2.5 rounded-full ring-4 ring-white" style="background-color: {{ $item['icon'] }};"></div>
                                        <div>
                                            <div class="text-sm font-bold text-slate-800">{{ $item['title'] }}</div>
                                            <div class="mt-0.5 text-xs font-medium text-slate-500">{{ $item['time'] }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
"""

content = profile_old_pattern.sub(profile_new, content)

# 4. Replace other occurrences of `card-padded` and `card` inside Attendance, Results, Finance, Analytics tabs
# with `rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100`

# Replace specifically `card-padded` inside those blocks
content = content.replace('class="card-padded"', 'class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100"')
content = content.replace("class='card-padded'", 'class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100"')

# Replace `class="card"` but avoid interfering with custom cards we just added
# To be safe, let's use regex to replace `<div class="card">` that are left
content = re.sub(r'<div class="card">', r'<div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 overflow-hidden">', content)

# Brand colors update in table headers
content = content.replace('bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500', 'bg-slate-50/80 text-xs font-semibold uppercase tracking-wider text-slate-500 border-b border-slate-100')
content = content.replace('divide-gray-100', 'divide-slate-100')
content = content.replace('text-gray-900', 'text-slate-800')
content = content.replace('text-gray-700', 'text-slate-600')
content = content.replace('text-gray-600', 'text-slate-500')
content = content.replace('text-gray-500', 'text-slate-400')
content = content.replace('bg-brand-50', 'bg-orange-50')
content = content.replace('ring-brand-100', 'ring-orange-100')
content = content.replace('text-brand-700', 'text-orange-600')

with open('resources/views/pages/students/show.blade.php', 'w', encoding='utf-8') as f:
    f.write(content)
