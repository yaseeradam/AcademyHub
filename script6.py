import os
import re

file_path = 'resources/views/pages/students/show.blade.php'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Replace the dark hero section
dark_hero_start = '{{-- Hero Card (Inherited directly from dashboard.blade.php style) --}}'
dark_hero_end = '{{-- Tab Navigation (Soft pill style) --}}'

dark_hero_regex = re.compile(f'{re.escape(dark_hero_start)}.*?(?={re.escape(dark_hero_end)})', re.DOTALL)

light_hero = """{{-- Profile Header Card --}}
    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 px-6 py-6 mb-2">
        <div class="flex flex-col gap-6 md:flex-row md:items-center justify-between">
            {{-- Left: Avatar & Info --}}
            <div class="flex items-center gap-5">
                <div class="shrink-0 relative">
                    @if ($student->passport_photo_url)
                        <img src="{{ $student->passport_photo_url }}" alt="{{ $student->full_name }}" class="h-20 w-20 rounded-2xl object-cover ring-4 ring-slate-50 shadow-sm" />
                    @else
                        <div class="grid h-20 w-20 place-items-center rounded-2xl bg-gradient-to-br from-orange-400 to-amber-500 text-2xl font-bold text-white shadow-sm ring-4 ring-slate-50">
                            {{ $initials }}
                        </div>
                    @endif
                </div>
                
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-2xl font-bold text-slate-800 tracking-tight">{{ $student->full_name }}</h2>
                        @php $statusColor = match($student->status) { 'Active' => 'bg-emerald-50 text-emerald-600 ring-emerald-200', 'Graduated' => 'bg-emerald-50 text-emerald-600 ring-emerald-200', default => 'bg-amber-50 text-amber-600 ring-amber-200' }; @endphp
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 {{ $statusColor }}">{{ $student->status }}</span>
                    </div>
                    <p class="mt-1 text-sm font-medium text-slate-500">{{ $studentMeta }}</p>
                    
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('students.admission-form', $student) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-orange-50 px-3 py-1.5 text-xs font-bold text-orange-600 hover:bg-orange-100 transition">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                            Admission Form
                        </a>
                        @if (auth()->user()?->role === 'admin')
                            <a href="{{ route('students.edit', $student) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-50 transition shadow-sm">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit Profile
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Right: Actions & Status --}}
            <div class="flex md:flex-col items-center md:items-end justify-between h-full gap-4">
                <a href="{{ route('students.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-50 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 transition ring-1 ring-slate-200 shadow-sm">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    Back to List
                </a>
                
                @if (auth()->user()?->role === 'admin')
                    <form method="POST" action="{{ route('students.destroy', $student) }}" class="mt-1" onsubmit="return confirm('Delete this student?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs font-semibold text-red-500 hover:text-red-700 hover:underline underline-offset-2 transition-colors">Delete Student</button>
                    </form>
                @endif
            </div>
        </div>
    </div>


    """

content = dark_hero_regex.sub(light_hero, content)

# I should also fix the tab design. The user probably liked the previous light navigation pill tab design rather than the dark slate-800 tabs.
tab_old_start = '{{-- Tab Navigation (Soft pill style) --}}'
tab_old_end = '@if ($errors->any())'

tab_regex = re.compile(f'{re.escape(tab_old_start)}.*?(?={re.escape(tab_old_end)})', re.DOTALL)

tab_new = """{{-- Tab Navigation --}}
    <div class="rounded-2xl bg-white p-1.5 shadow-sm ring-1 ring-slate-100 mb-6">
        <div class="flex gap-1 overflow-x-auto">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('students.show', ['student' => $student, 'tab' => $key]) }}"
                    class="{{ $tab === $key ? 'bg-gradient-to-br from-orange-400 to-amber-500 text-white shadow-md shadow-orange-200' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' }} flex-1 min-w-[100px] rounded-xl py-2.5 text-center text-sm font-bold transition whitespace-nowrap">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    """

content = tab_regex.sub(tab_new, content)


with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)
