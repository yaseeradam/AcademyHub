<?php

namespace App\Http\Controllers;

use App\Support\Audit;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\SubjectAllocation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    public function create()
    {
        return view('pages.teachers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->where('tenant_id', \App\Support\TenantSettings::tenantId())],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
            'is_class_teacher' => ['nullable', 'boolean'],
            'photo'     => ['nullable', 'image', 'max:2048'],
        ]);

        $profilePhotoPath = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $ext = $file->getClientOriginalExtension() ?: 'jpg';
            $safeName = Str::of($data['name'])->lower()->replaceMatches('/[^a-z0-9]+/i', '-')->trim('-')->toString();
            $filename = ($safeName ?: 'teacher').'-'.now()->format('YmdHis').'-'.bin2hex(random_bytes(3)).'.'.$ext;
            $profilePhotoPath = $file->storeAs('teacher-photos', $filename, 'uploads');
            $profilePhotoPath = str_replace('\\', '/', (string) $profilePhotoPath);
        }

        $teacher = User::query()->create([
            'name'             => $data['name'],
            'email'            => $data['email'],
            'password'         => $data['password'],
            'role'             => 'teacher',
            'is_active'        => (bool) ($data['is_active'] ?? false),
            'is_class_teacher' => (bool) ($data['is_class_teacher'] ?? false),
            'profile_photo'    => $profilePhotoPath,
        ]);

        return redirect()
            ->route('teachers.show', $teacher)
            ->with('success', 'Teacher added successfully.');
    }

    public function show(User $teacher)
    {
        abort_unless($teacher->role === 'teacher', 404);

        $allocations = SubjectAllocation::query()
            ->with(['subject', 'schoolClass'])
            ->where('teacher_id', $teacher->id)
            ->orderBy('class_id')
            ->orderBy('subject_id')
            ->get();

        $classes = SchoolClass::query()->orderBy('level')->get();
        $subjects = Subject::query()->orderBy('name')->get();

        return view('pages.teachers.show', compact('teacher', 'allocations', 'classes', 'subjects'));
    }

    public function edit(User $teacher)
    {
        abort_unless($teacher->role === 'teacher', 404);

        return view('pages.teachers.edit', compact('teacher'));
    }

    public function update(Request $request, User $teacher)
    {
        abort_unless($teacher->role === 'teacher', 404);

        $data = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->where('tenant_id', \App\Support\TenantSettings::tenantId())->ignore($teacher->id)],
            'password'         => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active'        => ['nullable', 'boolean'],
            'is_class_teacher' => ['nullable', 'boolean'],
        ]);

        $teacher->name             = $data['name'];
        $teacher->email            = $data['email'];
        $teacher->is_active        = (bool) ($data['is_active'] ?? false);
        $teacher->is_class_teacher = (bool) ($data['is_class_teacher'] ?? false);

        if (! empty($data['password'])) {
            $teacher->password = $data['password'];
        }

        $teacher->save();

        return redirect()
            ->route('teachers.show', $teacher)
            ->with('status', 'Teacher updated.');
    }

    public function updatePhoto(Request $request, User $teacher)
    {
        abort_unless($teacher->role === 'teacher', 404);

        $data = $request->validate([
            'photo' => ['required', 'image', 'max:2048'],
        ]);

        $file = $data['photo'];
        $ext = $file->getClientOriginalExtension() ?: 'jpg';

        $safeName = Str::of($teacher->name)->lower()->replaceMatches('/[^a-z0-9]+/i', '-')->trim('-')->toString();
        $filename = ($safeName ?: 'teacher').'-'.$teacher->id.'-'.now()->format('YmdHis').'-'.bin2hex(random_bytes(3)).'.'.$ext;
        $path = $file->storeAs('teacher-photos', $filename, 'uploads');
        $path = str_replace('\\', '/', (string) $path);

        $old = $teacher->profile_photo ? str_replace('\\', '/', (string) $teacher->profile_photo) : null;
        if ($old && $old !== $path) {
            Storage::disk('uploads')->delete($old);
        }

        $teacher->profile_photo = $path;
        $teacher->save();

        Audit::log('teacher.photo_updated', $teacher, ['path' => $path]);

        return back()->with('status', 'Profile photo updated.');
    }

    public function storeAllocation(Request $request, User $teacher)
    {
        abort_unless($teacher->role === 'teacher', 404);

        $data = $request->validate([
            'class_id' => ['nullable', 'required_without:class_ids', 'integer', 'exists:classes,id'],
            'class_ids' => ['nullable', 'required_without:class_id', 'array', 'min:1'],
            'class_ids.*' => ['integer', 'distinct', 'exists:classes,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
        ]);

        $classIds = collect($data['class_ids'] ?? (! empty($data['class_id']) ? [$data['class_id']] : []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $created = 0;
        foreach ($classIds as $classId) {
            try {
                $allocation = SubjectAllocation::query()->firstOrCreate([
                    'teacher_id' => (int) $teacher->id,
                    'class_id' => $classId,
                    'subject_id' => (int) $data['subject_id'],
                ]);

                if ($allocation->wasRecentlyCreated) {
                    $created++;
                }
            } catch (QueryException $e) {
                // Ignore duplicate allocation attempts (unique constraint).
            }
        }

        if ($created === 0) {
            return back()->with('status', 'All selected allocations already exist.');
        }

        $message = $created === 1 ? '1 allocation saved.' : "{$created} allocations saved.";

        return back()->with('status', $message);
    }

    public function destroyAllocation(User $teacher, SubjectAllocation $allocation)
    {
        abort_unless($teacher->role === 'teacher', 404);
        abort_unless((int) $allocation->teacher_id === (int) $teacher->id, 404);

        $allocation->delete();

        return back()->with('status', 'Allocation removed.');
    }

    public function destroy(User $teacher)
    {
        abort_unless($teacher->role === 'teacher', 404);

        $photo = $teacher->profile_photo ? str_replace('\\', '/', (string) $teacher->profile_photo) : null;

        try {
            $teacher->delete();
        } catch (QueryException $e) {
            return back()->withErrors(['teacher' => 'Unable to delete this teacher. Remove dependent records first.']);
        }

        if ($photo) {
            Storage::disk('uploads')->delete($photo);
        }

        return redirect()
            ->route('teachers')
            ->with('status', 'Teacher deleted.');
    }
}
