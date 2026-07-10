<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassNote extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'class_id',
        'subject_id',
        'user_id',
        'title',
        'description',
        'term_name',
        'file_name',
        'file_path',
        'file_size',
        'downloads',
    ];

    protected $casts = [
        'tenant_id'  => 'integer',
        'class_id'   => 'integer',
        'subject_id' => 'integer',
        'user_id'    => 'integer',
        'file_size'  => 'integer',
        'downloads'  => 'integer',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
