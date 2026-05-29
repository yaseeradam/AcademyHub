<?php

namespace App\Observers;

use App\Models\Score;
use App\Support\WhatsAppService;

class ScoreObserver
{
    /**
     * Listen to the saved event of Score.
     *
     * @param Score $score
     * @return void
     */
    public function saved(Score $score): void
    {
        $student = $score->student;
        if ($student) {
            \App\Support\StudentPerformanceService::clearCache($student->id);
        }

        $subject = $score->subject;
        if (!$student || !$subject) {
            return;
        }

        // Fetch all parents who are subscribed to WhatsApp notifications
        $parents = $student->parents()
            ->where('whatsapp_subscribed', true)
            ->whereNotNull('whatsapp_phone')
            ->get();

        foreach ($parents as $parent) {
            $message = "📊 *New Grade Released:* A new score has been published for *{$student->full_name}* in *{$subject->name}*:\n\n" .
                       "• *CA 1:* {$score->ca1}\n" .
                       "• *CA 2:* {$score->ca2}\n" .
                       "• *Exam:* {$score->exam}\n" .
                       "• *Total Score:* *{$score->total}/100*\n" .
                       "• *Grade:* *{$score->grade}*";
            
            WhatsAppService::sendMessage($parent->whatsapp_phone, $message);
        }
    }

    /**
     * Listen to the deleted event of Score.
     *
     * @param Score $score
     * @return void
     */
    public function deleted(Score $score): void
    {
        if ($score->student_id) {
            \App\Support\StudentPerformanceService::clearCache($score->student_id);
        }
    }
}
