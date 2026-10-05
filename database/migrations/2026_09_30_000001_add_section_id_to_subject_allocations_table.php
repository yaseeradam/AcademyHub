<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function indexExists(string $table, string $indexName): bool
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return false;
        }

        $database = DB::getDatabaseName();

        $row = DB::selectOne(
            'SELECT 1 FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?
             LIMIT 1',
            [$database, $table, $indexName],
        );

        return (bool) $row;
    }

    public function up(): void
    {
        if (Schema::hasTable('subject_allocations') && !Schema::hasColumn('subject_allocations', 'section_id')) {
            Schema::table('subject_allocations', function (Blueprint $table) {
                $table->foreignId('section_id')
                    ->nullable()
                    ->after('class_id')
                    ->constrained('sections')
                    ->nullOnDelete();
            });
        }

        // For non-sqlite databases, ensure teacher_id index exists before dropping old unique constraint
        if (DB::connection()->getDriverName() !== 'sqlite') {
            $teacherIndex = 'subject_allocations_teacher_id_index';
            if (! $this->indexExists('subject_allocations', $teacherIndex)) {
                Schema::table('subject_allocations', function (Blueprint $table) use ($teacherIndex) {
                    $table->index('teacher_id', $teacherIndex);
                });
            }

            $oldIndex = 'subject_allocations_teacher_id_subject_id_class_id_unique';
            if ($this->indexExists('subject_allocations', $oldIndex)) {
                Schema::table('subject_allocations', function (Blueprint $table) use ($oldIndex) {
                    $table->dropUnique($oldIndex);
                });
            }

            $newIndex = 'sub_alloc_t_c_s_sec_unique';
            if (! $this->indexExists('subject_allocations', $newIndex)) {
                Schema::table('subject_allocations', function (Blueprint $table) use ($newIndex) {
                    $table->unique(['teacher_id', 'class_id', 'section_id', 'subject_id'], $newIndex);
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('subject_allocations')) {
            if (DB::connection()->getDriverName() !== 'sqlite') {
                $newIndex = 'sub_alloc_t_c_s_sec_unique';
                if ($this->indexExists('subject_allocations', $newIndex)) {
                    Schema::table('subject_allocations', function (Blueprint $table) use ($newIndex) {
                        $table->dropUnique($newIndex);
                    });
                }

                $oldIndex = 'subject_allocations_teacher_id_subject_id_class_id_unique';
                if (! $this->indexExists('subject_allocations', $oldIndex)) {
                    Schema::table('subject_allocations', function (Blueprint $table) use ($oldIndex) {
                        $table->unique(['teacher_id', 'subject_id', 'class_id'], $oldIndex);
                    });
                }
            }

            if (Schema::hasColumn('subject_allocations', 'section_id')) {
                Schema::table('subject_allocations', function (Blueprint $table) {
                    $table->dropForeign(['section_id']);
                    $table->dropColumn('section_id');
                });
            }
        }
    }
};
