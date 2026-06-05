@php
    use App\Models\SchoolClass;
    use App\Models\Section;
    use App\Models\Student;
    use App\Models\Subject;
    use App\Models\SubjectAllocation;

    $schoolName = config('academyhub.school_name', config('app.name', 'AcademyHub'));

    $classRows = SchoolClass::query()
        ->withCount(['sections', 'students'])
        ->orderBy('level')
        ->get();

    $stats = [
        'Classes' => SchoolClass::query()->count(),
        'Sections' => Section::query()->count(),
        'Students' => Student::query()->count(),
        'Subjects' => Subject::query()->count(),
        'Allocations' => SubjectAllocation::query()->count(),
    ];
@endphp

@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <x-page-header
            title="Institute"
            subtitle="School structure, classes, and subject setup for {{ $schoolName }}."
            accent="institute"
        />

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            @foreach ($stats as $label => $value)
                <x-stat-card :label="$label" :value="number_format((int) $value)" />
            @endforeach
        </div>

        <x-table>
            <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                <tr>
                    <th class="px-5 py-3">Class</th>
                    <th class="px-5 py-3 text-right">Sections</th>
                    <th class="px-5 py-3 text-right">Students</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($classRows as $row)
                    <tr class="bg-white hover:bg-gray-50">
                        <td class="px-5 py-4">
                            <div class="text-sm font-semibold text-gray-900">{{ $row->name }}</div>
                            <div class="mt-1 text-xs text-gray-500">Level {{ $row->level }}</div>
                        </td>
                        <td class="px-5 py-4 text-right text-sm font-semibold text-gray-900">
                            {{ number_format((int) $row->sections_count) }}
                        </td>
                        <td class="px-5 py-4 text-right text-sm font-semibold text-gray-900">
                            {{ number_format((int) $row->students_count) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-5 py-10 text-center text-sm text-gray-500">
                            No classes yet. Run the database seeder to generate demo data.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-table>
    </div>
@endsection
