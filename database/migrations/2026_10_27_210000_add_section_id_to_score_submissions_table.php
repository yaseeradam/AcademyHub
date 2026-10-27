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
        if (Schema::hasTable('score_submissions') && !Schema::hasColumn('score_submissions', 'section_id')) {
            Schema::table('score_submissions', function (Blueprint $table) {
                $table->foreignId('section_id')
                    ->nullable()
                    ->after('class_id')
                    ->constrained('sections')
                    ->nullOnDelete();
            });
        }

        if (DB::connection()->getDriverName() !== 'sqlite') {
            $teacherIndex = 'score_submissions_teacher_id_index';
            if (! $this->indexExists('score_submissions', $teacherIndex)) {
                Schema::table('score_submissions', function (Blueprint $table) use ($teacherIndex) {
                    $table->index('teacher_id', $teacherIndex);
                });
            }

            $oldIndex = 'score_submissions_unique';
            if ($this->indexExists('score_submissions', $oldIndex)) {
                Schema::table('score_submissions', function (Blueprint $table) use ($oldIndex) {
                    $table->dropUnique($oldIndex);
                });
            }

            $newIndex = 'score_sub_t_c_sec_sub_t_s_unique';
            if (! $this->indexExists('score_submissions', $newIndex)) {
                Schema::table('score_submissions', function (Blueprint $table) use ($newIndex) {
                    $table->unique(['teacher_id', 'class_id', 'section_id', 'subject_id', 'term', 'session'], $newIndex);
                });
            }

            $lookupIndex = 'score_sub_class_sec_sub_lookup';
            if (! $this->indexExists('score_submissions', $lookupIndex)) {
                Schema::table('score_submissions', function (Blueprint $table) use ($lookupIndex) {
                    $table->index(['class_id', 'section_id', 'subject_id', 'term', 'session'], $lookupIndex);
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('score_submissions')) {
            if (DB::connection()->getDriverName() !== 'sqlite') {
                $lookupIndex = 'score_sub_class_sec_sub_lookup';
                if ($this->indexExists('score_submissions', $lookupIndex)) {
                    Schema::table('score_submissions', function (Blueprint $table) use ($lookupIndex) {
                        $table->dropIndex($lookupIndex);
                    });
                }

                $newIndex = 'score_sub_t_c_sec_sub_t_s_unique';
                if ($this->indexExists('score_submissions', $newIndex)) {
                    Schema::table('score_submissions', function (Blueprint $table) use ($newIndex) {
                        $table->dropUnique($newIndex);
                    });
                }

                $oldIndex = 'score_submissions_unique';
                if (! $this->indexExists('score_submissions', $oldIndex)) {
                    Schema::table('score_submissions', function (Blueprint $table) use ($oldIndex) {
                        $table->unique(['teacher_id', 'class_id', 'subject_id', 'term', 'session'], $oldIndex);
                    });
                }
            }

            if (Schema::hasColumn('score_submissions', 'section_id')) {
                Schema::table('score_submissions', function (Blueprint $table) {
                    $table->dropForeign(['section_id']);
                    $table->dropColumn('section_id');
                });
            }
        }
    }
};
