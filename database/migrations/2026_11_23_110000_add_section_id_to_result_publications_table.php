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
            [$database, $table, $indexName]
        );

        return (bool) $row;
    }

    public function up(): void
    {
        if (Schema::hasTable('result_publications') && ! Schema::hasColumn('result_publications', 'section_id')) {
            Schema::table('result_publications', function (Blueprint $table) {
                $table->foreignId('section_id')
                    ->nullable()
                    ->after('class_id')
                    ->constrained('sections')
                    ->nullOnDelete();
            });
        }

        if (DB::connection()->getDriverName() !== 'sqlite') {
            // First add an index on class_id so the foreign key requirement is satisfied before dropping the old unique index
            $classIndex = 'result_publications_class_id_index';
            if (! $this->indexExists('result_publications', $classIndex)) {
                Schema::table('result_publications', function (Blueprint $table) use ($classIndex) {
                    $table->index('class_id', $classIndex);
                });
            }

            $newUnique = 'result_pub_c_sec_t_s_unique';
            if (! $this->indexExists('result_publications', $newUnique)) {
                Schema::table('result_publications', function (Blueprint $table) use ($newUnique) {
                    $table->unique(['class_id', 'section_id', 'term', 'session'], $newUnique);
                });
            }

            $oldUnique = 'result_publications_class_id_term_session_unique';
            if ($this->indexExists('result_publications', $oldUnique)) {
                Schema::table('result_publications', function (Blueprint $table) use ($oldUnique) {
                    $table->dropUnique($oldUnique);
                });
            }
        }

        // Clean up the publication for Nursery 1 (class_id=3) so it attaches to Gold (section_id=1)
        DB::table('result_publications')
            ->where('class_id', 3)
            ->whereNull('section_id')
            ->update(['section_id' => 1]);
    }

    public function down(): void
    {
        if (Schema::hasTable('result_publications')) {
            if (DB::connection()->getDriverName() !== 'sqlite') {
                $oldUnique = 'result_publications_class_id_term_session_unique';
                if (! $this->indexExists('result_publications', $oldUnique)) {
                    Schema::table('result_publications', function (Blueprint $table) use ($oldUnique) {
                        $table->unique(['class_id', 'term', 'session'], $oldUnique);
                    });
                }

                $newUnique = 'result_pub_c_sec_t_s_unique';
                if ($this->indexExists('result_publications', $newUnique)) {
                    Schema::table('result_publications', function (Blueprint $table) use ($newUnique) {
                        $table->dropUnique($newUnique);
                    });
                }

                $classIndex = 'result_publications_class_id_index';
                if ($this->indexExists('result_publications', $classIndex)) {
                    Schema::table('result_publications', function (Blueprint $table) use ($classIndex) {
                        $table->dropIndex($classIndex);
                    });
                }
            }

            if (Schema::hasColumn('result_publications', 'section_id')) {
                Schema::table('result_publications', function (Blueprint $table) {
                    $table->dropForeign(['section_id']);
                    $table->dropColumn('section_id');
                });
            }
        }
    }
};
