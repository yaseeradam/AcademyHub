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
        // Only trigger proactive notifications for 'Absent' status
        if ($mark->status !== 'A') {
            return;
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

        foreach ($parents as $parent) {
            $sheetDate = $mark->sheet && $mark->sheet->date 
                ? $mark->sheet->date->toDateString() 
                : today()->toDateString();

            $message = "🔔 *Attendance Alert:* Your child, *{$student->full_name}*, has been marked *Absent* today ({$sheetDate}). Please contact the school administration if this is an error.";
            WhatsAppService::sendMessage($parent->whatsapp_phone, $message);
        }
    }
}
