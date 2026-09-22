<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\SubjectAllocation;
use App\Support\TenantSettings;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;

class SchoolClassController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $classesQuery = SchoolClass::query()
            ->with('sections')
            ->withCount(['sections', 'students', 'defaultSubjects as subjects_count'])
            ->orderBy('level')
            ->orderBy('name');

        if ($user?->role === 'teacher') {
            $classIds = SubjectAllocation::query()
                ->where('teacher_id', $user->id)
                ->pluck('class_id')
                ->unique();

            if ($classIds->isEmpty()) {
                return view('pages.classes.index', ['classes' => collect()]);
            }

            $classesQuery->whereIn('id', $classIds);
        }

        $classes = $classesQuery->get();

        return view('pages.classes.index', compact('classes'));
    }

    public function manage()
    {
        $user = auth()->user();
        abort_unless($user?->role === 'admin', 403);

        $classes = SchoolClass::query()
            ->with('sections')
            ->withCount(['sections', 'students'])
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        return view('pages.classes.manage', compact('classes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('classes', 'name')->where('tenant_id', TenantSettings::tenantId())
            ],
            'level' => ['required', 'integer', 'min:1', 'max:30'],
        ]);

        SchoolClass::query()->create([
            'name' => $data['name'],
            'level' => (int) $data['level'],
        ]);

        return back()->with('modal', ['type' => 'success', 'message' => 'Class created successfully!']);
    }

    public function update(Request $request, SchoolClass $class)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('classes', 'name')
                    ->where('tenant_id', TenantSettings::tenantId())
                    ->ignore($class->id)
            ],
            'level' => ['required', 'integer', 'min:1', 'max:30'],
        ]);

        $class->update([
            'name' => $data['name'],
            'level' => (int) $data['level'],
        ]);

        return back()->with('modal', ['type' => 'success', 'message' => 'Class updated successfully!']);
    }

    public function destroy(SchoolClass $class)
    {
        try {
            $class->delete();
        } catch (QueryException $e) {
            return back()->withErrors(['class' => 'Unable to delete this class. Remove dependent records first.']);
        }

        return back()->with('modal', ['type' => 'success', 'message' => 'Class deleted successfully!']);
    }
}
