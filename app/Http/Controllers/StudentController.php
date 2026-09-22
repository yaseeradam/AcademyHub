<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\SubjectAllocation;
use App\Models\AttendanceMark;
use App\Models\StudentNotification;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function show(Student $student): View
    {
        $user = auth()->user();
        if ($user?->role === 'teacher') {
            $allowed = SubjectAllocation::query()
                ->where('teacher_id', $user->id)
                ->where('class_id', $student->class_id)
                ->exists();

            abort_unless($allowed, 403);
        }

        $student->load(['schoolClass', 'section']);

        return view('pages.students.show', [
            'student' => $student,
        ]);
    }

    public function destroy(Student $student)
    {
        $photo = $student->passport_photo ? str_replace('\\', '/', (string) $student->passport_photo) : null;

        try {
            DB::transaction(function () use ($student) {
                // Detach pivots
                $student->parents()->detach();
                $student->subjectOverrides()->detach();

                // Clean up attendance marks
                AttendanceMark::query()
                    ->where('student_id', $student->id)
                    ->delete();

                // Clean up student notifications
                StudentNotification::query()
                    ->where('student_id', $student->id)
                    ->delete();

                // Explicitly delete academic scores
                \App\Models\Score::query()
                    ->where('student_id', $student->id)
                    ->delete();

                // Explicitly delete CBT attempts & answers (to trigger observers and clear cache)
                $attempts = \App\Models\CbtAttempt::query()
                    ->where('student_id', $student->id)
                    ->get();
                foreach ($attempts as $attempt) {
                    \App\Models\CbtAnswer::query()
                        ->where('attempt_id', $attempt->id)
                        ->delete();
                    $attempt->delete();
                }

                // Explicitly delete homework submissions & attachment files (to trigger observers, clear cache, and free disk space)
                $submissions = \App\Models\HomeworkSubmission::query()
                    ->where('student_id', $student->id)
                    ->get();
                foreach ($submissions as $submission) {
                    if ($submission->attachment) {
                        $attachmentPath = str_replace('\\', '/', (string) $submission->attachment);
                        Storage::disk('uploads')->delete($attachmentPath);
                    }
                    $submission->delete();
                }

                // Explicitly delete certificates
                \App\Models\Certificate::query()
                    ->where('student_id', $student->id)
                    ->delete();

                // Explicitly delete psychomotor scores
                \App\Models\PsychomotorScore::query()
                    ->where('student_id', $student->id)
                    ->delete();

                // Explicitly delete class note comments
                \App\Models\ClassNoteComment::query()
                    ->where('student_id', $student->id)
                    ->delete();

                // Explicitly delete promotions
                \App\Models\Promotion::query()
                    ->where('student_id', $student->id)
                    ->delete();

                $student->delete();
            });
        } catch (QueryException $e) {
            return back()->withErrors(['student' => 'Unable to delete this student. Remove dependent records first.']);
        }

        if ($photo) {
            Storage::disk('uploads')->delete($photo);
        }

        return redirect()
            ->route('students.index')
            ->with('status', 'Student deleted.');
    }
}
