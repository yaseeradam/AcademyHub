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

            $maxTotal =
                max(0, (int) config('myacademy.results_ca1_max', 20))
                + max(0, (int) config('myacademy.results_ca2_max', 20))
                + max(0, (int) config('myacademy.results_exam_max', 60));

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
