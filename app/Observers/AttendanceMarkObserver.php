<?php

namespace App\Observers;

use App\Models\AttendanceMark;
use App\Support\WhatsAppService;

class AttendanceMarkObserver
{
    /**
     * Listen to the saved event of AttendanceMark.
     *
     * @param AttendanceMark $mark
     * @return void
     */
    public function saved(AttendanceMark $mark): void
    {
        if ($mark->student_id) {
            \App\Support\StudentPerformanceService::clearCache($mark->student_id);
        }

        $student = $mark->student;
        if (!$student) {
            return;
        }

        // Fetch all parents who are subscribed to WhatsApp notifications
        $parents = $student->parents()
            ->where('whatsapp_subscribed', true)
            ->whereNotNull('whatsapp_phone')
            ->get();

        if ($parents->isEmpty()) {
            return;
        }

        $sheetDate = ($mark->sheet && $mark->sheet->date) 
            ? $mark->sheet->date->toDateString() 
            : today()->toDateString();

        $statusClean = strtoupper(trim((string) $mark->status));

        $statusText = match ($statusClean) {
            'P', 'PRESENT' => 'Present',
            'L', 'LATE' => 'Late',
            'A', 'ABSENT' => 'Absent',
            'E', 'EXCUSED' => 'Excused',
            default => $mark->status
        };

        $icon = match ($statusText) {
            'Present' => '✅',
            'Late' => '⚠️',
            'Absent' => '❌',
            'Excused' => 'ℹ️',
            default => '📅'
        };

        if ($statusText === 'Present') {
            $message = "{$icon} *Attendance Alert:* Your child, *{$student->full_name}*, has been marked *Present* in school today ({$sheetDate}).";
        } elseif ($statusText === 'Late') {
            $message = "{$icon} *Attendance Alert:* Your child, *{$student->full_name}*, was marked *Late* in school today ({$sheetDate}).";
        } elseif ($statusText === 'Absent') {
            $message = "{$icon} *Attendance Alert:* Your child, *{$student->full_name}*, has been marked *Absent* today ({$sheetDate}). Please contact the school administration if this is an error.";
        } else {
            $message = "{$icon} *Attendance Alert:* Your child, *{$student->full_name}*, attendance status is marked *{$statusText}* today ({$sheetDate}).";
        }

        foreach ($parents as $parent) {
            WhatsAppService::sendMessage($parent->whatsapp_phone, $message);
        }
    }

    /**
     * Listen to the deleted event of AttendanceMark.
     *
     * @param AttendanceMark $mark
     * @return void
     */
    public function deleted(AttendanceMark $mark): void
    {
        if ($mark->student_id) {
            \App\Support\StudentPerformanceService::clearCache($mark->student_id);
        }
    }
}
