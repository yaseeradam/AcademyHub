<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'student_id',
        'type',
        'title',
        'body',
        'description',
        'session',
        'term',
        'issue_date',
        'issued_on',
        'serial_number',
        'template',
        'issued_by',
    ];

    protected $casts = [
        'tenant_id' => 'integer',
        'student_id' => 'integer',
        'issued_by' => 'integer',
        'term' => 'integer',
        'issued_on' => 'date',
        'issue_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
