<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Support\TenantSettings;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $subjectsQuery = Subject::query()->orderBy('name');

        if ($user?->role === 'teacher') {
            $subjectIds = \App\Models\SubjectAllocation::query()
                ->where('teacher_id', $user->id)
                ->pluck('subject_id')
                ->unique();

            if ($subjectIds->isEmpty()) {
                return view('pages.subjects.index', ['subjects' => collect()]);
            }

            $subjectsQuery->whereIn('id', $subjectIds);
        }

        $subjects = $subjectsQuery->get();

        return view('pages.subjects.index', compact('subjects'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('subjects', 'code')->where('tenant_id', TenantSettings::tenantId())
            ],
        ]);

        Subject::query()->create([
            'name' => $data['name'],
            'code' => strtoupper(trim($data['code'])),
        ]);

        return back()->with('modal', ['type' => 'success', 'message' => 'Subject created successfully!']);
    }

    public function update(Request $request, Subject $subject)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('subjects', 'code')
                    ->where('tenant_id', TenantSettings::tenantId())
                    ->ignore($subject->id)
            ],
        ]);

        $subject->update([
            'name' => $data['name'],
            'code' => strtoupper(trim($data['code'])),
        ]);

        return back()->with('modal', ['type' => 'success', 'message' => 'Subject updated successfully!']);
    }

    public function destroy(Subject $subject)
    {
        try {
            $subject->delete();
        } catch (QueryException $e) {
            return back()->withErrors(['subject' => 'Unable to delete this subject. Remove dependent records first.']);
        }

        return back()->with('modal', ['type' => 'success', 'message' => 'Subject deleted successfully!']);
    }
}
