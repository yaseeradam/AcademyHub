<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceMark;
use App\Models\AttendanceSheet;
use App\Models\Score;
use App\Models\SubjectAllocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncController extends Controller
{
    public function handleSync(Request $request)
    {
        $mutations = $request->input('mutations', []);

        if (empty($mutations)) {
            return response()->json(['success_ids' => []]);
        }

        $successIds = [];
        $failedIds  = [];

        DB::beginTransaction();
        try {
            foreach ($mutations as $mutation) {
                $id       = $mutation['id'] ?? null;
                $endpoint = $mutation['endpoint'] ?? '';
                $action   = strtoupper($mutation['action'] ?? '');
                $payload  = $mutation['payload'] ?? [];

                if (!$id || !$endpoint) continue;

                try {
                    if (str_contains($endpoint, 'attendance')) {
                        $this->syncAttendance($request->user(), $payload);
                    } elseif (str_contains($endpoint, 'scores')) {
                        $this->syncScores($request->user(), $payload);
                    } elseif (str_contains($endpoint, 'announcements')) {
                        $this->syncAnnouncement($request->user(), $payload);
                    }
                    $successIds[] = $id;
                } catch (\Exception $e) {
                    Log::error("Sync failed for mutation $id", ['error' => $e->getMessage()]);
                    $failedIds[] = $id;
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fatal sync error', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        return response()->json(['status' => 'success', 'success_ids' => $successIds, 'failed_ids' => $failedIds]);
    }

    private function syncAttendance($user, array $payload): void
    {
        $classId = $payload['class_id'] ?? null;
        abort_unless($classId && $this->teacherOwnsClass($user, $classId), 403);

        $sheet = AttendanceSheet::firstOrCreate(
            [
                'class_id' => $classId,
                'date'     => $payload['date'],
                'term'     => $payload['term'],
                'session'  => $payload['session'],
            ],
            ['taken_by' => $user->id]
        );

        foreach ($payload['marks'] ?? [] as $mark) {
            $existing = AttendanceMark::where(['sheet_id' => $sheet->id, 'student_id' => $mark['student_id']])->first();
            
            if ($existing && isset($mark['last_known_updated_at'])) {
                $clientTime = \Carbon\Carbon::parse($mark['last_known_updated_at']);
                if ($existing->updated_at->gt($clientTime->addSeconds(1))) { // 1 sec tolerance
                    throw new \Exception("Attendance update conflict for student ID {$mark['student_id']}. Server has a newer record.");
                }
            }

            AttendanceMark::updateOrCreate(
                ['sheet_id' => $sheet->id, 'student_id' => $mark['student_id']],
                ['status' => $mark['status'], 'note' => $mark['note'] ?? null]
            );
        }
    }

    private function syncScores($user, array $payload): void
    {
        foreach ($payload['scores'] ?? [] as $s) {
            abort_unless($this->teacherOwnsClass($user, $s['class_id']), 403);

            $existing = Score::where([
                'student_id' => $s['student_id'],
                'subject_id' => $s['subject_id'],
                'class_id'   => $s['class_id'],
                'term'       => $s['term'],
                'session'    => $s['session'],
            ])->first();

            if ($existing && isset($s['last_known_updated_at'])) {
                $clientTime = \Carbon\Carbon::parse($s['last_known_updated_at']);
                if ($existing->updated_at->gt($clientTime->addSeconds(1))) {
                    throw new \Exception("Score update conflict for student ID {$s['student_id']}. Server has a newer record.");
                }
            }

            Score::updateOrCreate(
                [
                    'student_id' => $s['student_id'],
                    'subject_id' => $s['subject_id'],
                    'class_id'   => $s['class_id'],
                    'term'       => $s['term'],
                    'session'    => $s['session'],
                ],
                ['ca1' => $s['ca1'] ?? 0, 'ca2' => $s['ca2'] ?? 0, 'exam' => $s['exam'] ?? 0]
            );
        }
    }

    private function teacherOwnsClass($user, int $classId): bool
    {
        if ($user->role === 'admin') return true;
        return SubjectAllocation::where('teacher_id', $user->id)->where('class_id', $classId)->exists();
    }

    private function syncAnnouncement($user, array $payload): void
    {
        abort_unless($user->role === 'admin' || $user->role === 'teacher', 403);
        \App\Models\Announcement::create([
            'title' => $payload['title'],
            'body' => $payload['body'],
            'audience' => $payload['audience'] ?? 'all',
            'published_at' => now(),
            'created_by' => $user->id,
        ]);
    }
}
