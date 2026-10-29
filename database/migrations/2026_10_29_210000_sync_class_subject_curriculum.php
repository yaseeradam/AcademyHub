<?php

use App\Models\SchoolClass;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $classes = SchoolClass::all();

        foreach ($classes as $class) {
            // Get all distinct subjects allocated to this class in subject_allocations
            $allocatedSubjectIds = DB::table('subject_allocations')
                ->where('class_id', $class->id)
                ->distinct()
                ->pluck('subject_id')
                ->toArray();

            // Get existing default curriculum subjects
            $existingSubjectIds = DB::table('class_subject')
                ->where('class_id', $class->id)
                ->pluck('subject_id')
                ->toArray();

            $mergedIds = array_unique(array_merge($allocatedSubjectIds, $existingSubjectIds));

            $tenantId = $class->tenant_id ?? 1;

            foreach ($mergedIds as $subjectId) {
                DB::table('class_subject')->updateOrInsert(
                    [
                        'class_id'   => $class->id,
                        'subject_id' => $subjectId,
                    ],
                    [
                        'tenant_id'  => $tenantId,
                        'is_core'    => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        // No destructive action on rollback
    }
};
