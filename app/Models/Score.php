<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    use HasFactory;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'student_id',
        'subject_id',
        'class_id',
        'term',
        'session',
        'ca1',
        'ca2',
        'exam',
        'total',
        'grade',
        'position',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'student_id' => 'integer',
        'subject_id' => 'integer',
        'class_id' => 'integer',
        'term' => 'integer',
        'session' => 'string',
        'ca1' => 'integer',
        'ca2' => 'integer',
        'exam' => 'integer',
        'total' => 'integer',
        'position' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $score) {
            $score->total = (int) $score->ca1 + (int) $score->ca2 + (int) $score->exam;

            if ($score->session) {
                $score->session = trim((string) $score->session);
            }

            $maxTotal =
                max(0, (int) config('academyhub.results_ca1_max', 20))
                + max(0, (int) config('academyhub.results_ca2_max', 20))
                + max(0, (int) config('academyhub.results_exam_max', 60));

            $score->grade = self::gradeForTotal($score->total, $maxTotal);
        });
    }

    public static function gradeForTotal(int $total, int $maxTotal = 100): string
    {
        if ($maxTotal <= 0) {
            $maxTotal = 100;
        }

        $percent = (int) round(($total / $maxTotal) * 100);

        return match (true) {
            $percent >= 70 => 'A',
            $percent >= 60 => 'B',
            $percent >= 50 => 'C',
            $percent >= 45 => 'D',
            $percent >= 40 => 'E',
            default => 'F',
        };
    }

    public static function recalculatePositions(int $classId, int $subjectId, int $term, string $session): void
    {
        $scores = self::query()
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->where('term', $term)
            ->where('session', $session)
            ->orderByDesc('total')
            ->get();

        if ($scores->isEmpty()) {
            return;
        }

        $rank = 0;
        $prevTotal = null;
        $count = 0;

        foreach ($scores as $s) {
            $count++;
            if ($prevTotal === null || $s->total < $prevTotal) {
                $rank = $count;
                $prevTotal = $s->total;
            }
            if ($s->position !== $rank) {
                self::whereKey($s->id)->update(['position' => $rank]);
            }
        }
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}
