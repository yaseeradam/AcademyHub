<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            return false;
        }

        $database = DB::getDatabaseName();

        $row = DB::selectOne(
            'SELECT 1 FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL
             LIMIT 1',
            [$database, $table, $constraintName],
        );

        return (bool) $row;
    }

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
        // Add tenant_id to tenant-owned tables. Nullable for safe rollout / backfill.
        $tables = [
            'academic_sessions',
            'academic_terms',
            'announcements',
            'attendance_marks',
            'attendance_sheets',
            'audit_logs',
            'cbt_answers',
            'cbt_attempts',
            'cbt_exams',
            'cbt_options',
            'cbt_questions',
            'certificates',
            'classes',
            'class_subject',
            'conversations',
            'conversation_user',
            'custom_fields',
            'fee_structures',
            'homework',
            'homework_submissions',
            'in_app_notifications',
            'messages',
            'parent_student',
            'promotions',
            'premium_devices',
            'premium_device_removals',
            'result_publications',
            'scores',
            'score_submissions',
            'sections',
            'school_events',
            'student_notifications',
            'students',
            'student_subject_overrides',
            'subject_allocations',
            'subjects',
            'teacher_attendance_marks',
            'teacher_attendance_sheets',
            'timetable_entries',
            'transactions',
            'weekly_data_collections',
            'whatsapp_logs',
        ];

        foreach ($tables as $tableName) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }

            if (! Schema::hasColumn($tableName, 'tenant_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->unsignedBigInteger('tenant_id')->nullable();
                });
            }

            // Ensure index + FK exist (migration may have partially run).
            $indexName = "{$tableName}_tenant_id_index";
            $fkName = "{$tableName}_tenant_id_foreign";

            if (! $this->indexExists($tableName, $indexName)) {
                Schema::table($tableName, function (Blueprint $table) use ($indexName) {
                    $table->index('tenant_id', $indexName);
                });
            }

            if (! $this->foreignKeyExists($tableName, $fkName)) {
                Schema::table($tableName, function (Blueprint $table) use ($fkName) {
                    $table->foreign('tenant_id', $fkName)
                        ->references('id')
                        ->on('tenants')
                        ->nullOnDelete();
                });
            }
        }

        // Fix unique constraints that must be per-tenant.
        if (Schema::hasTable('classes') && Schema::hasColumn('classes', 'tenant_id')) {
            Schema::table('classes', function (Blueprint $table) {
                $table->dropUnique('classes_name_unique');
                $table->unique(['tenant_id', 'name'], 'classes_tenant_name_unique');
            });
        }

        if (Schema::hasTable('students') && Schema::hasColumn('students', 'tenant_id')) {
            Schema::table('students', function (Blueprint $table) {
                $table->dropUnique('students_admission_number_unique');
                $table->unique(['tenant_id', 'admission_number'], 'students_tenant_admission_unique');
            });
        }

        if (Schema::hasTable('subjects') && Schema::hasColumn('subjects', 'tenant_id')) {
            Schema::table('subjects', function (Blueprint $table) {
                $table->dropUnique('subjects_code_unique');
                $table->unique(['tenant_id', 'code'], 'subjects_tenant_code_unique');
            });
        }

        if (Schema::hasTable('academic_sessions') && Schema::hasColumn('academic_sessions', 'tenant_id')) {
            Schema::table('academic_sessions', function (Blueprint $table) {
                $table->dropUnique('academic_sessions_name_unique');
                $table->unique(['tenant_id', 'name'], 'academic_sessions_tenant_name_unique');
            });
        }

        if (Schema::hasTable('certificates') && Schema::hasColumn('certificates', 'tenant_id')) {
            Schema::table('certificates', function (Blueprint $table) {
                $table->dropUnique('certificates_serial_number_unique');
                $table->unique(['tenant_id', 'serial_number'], 'certificates_tenant_serial_unique');
            });
        }

        if (Schema::hasTable('custom_fields') && Schema::hasColumn('custom_fields', 'tenant_id')) {
            Schema::table('custom_fields', function (Blueprint $table) {
                $table->dropUnique('custom_fields_name_unique');
                $table->unique(['tenant_id', 'name'], 'custom_fields_tenant_name_unique');
            });
        }

        if (Schema::hasTable('transactions') && Schema::hasColumn('transactions', 'tenant_id')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropUnique('transactions_receipt_number_unique');
                $table->unique(['tenant_id', 'receipt_number'], 'transactions_tenant_receipt_unique');
            });
        }

        if (Schema::hasTable('cbt_exams') && Schema::hasColumn('cbt_exams', 'tenant_id')) {
            Schema::table('cbt_exams', function (Blueprint $table) {
                $table->dropUnique('cbt_exams_access_code_unique');
                $table->unique(['tenant_id', 'access_code'], 'cbt_exams_tenant_access_code_unique');
            });
        }

        if (Schema::hasTable('premium_devices') && Schema::hasColumn('premium_devices', 'tenant_id')) {
            Schema::table('premium_devices', function (Blueprint $table) {
                $table->dropUnique('premium_devices_device_id_unique');
                $table->unique(['tenant_id', 'device_id'], 'premium_devices_tenant_device_unique');
            });
        }

        if (Schema::hasTable('teacher_attendance_sheets') && Schema::hasColumn('teacher_attendance_sheets', 'tenant_id')) {
            Schema::table('teacher_attendance_sheets', function (Blueprint $table) {
                $table->dropUnique('teacher_attendance_sheet_unique');
                $table->unique(['tenant_id', 'date', 'term', 'session'], 'teacher_attendance_sheet_unique');
            });
        }
    }

    public function down(): void
    {
        // Reverse unique changes first.
        if (Schema::hasTable('teacher_attendance_sheets')) {
            Schema::table('teacher_attendance_sheets', function (Blueprint $table) {
                if (Schema::hasColumn('teacher_attendance_sheets', 'tenant_id')) {
                    $table->dropUnique('teacher_attendance_sheet_unique');
                    $table->unique(['date', 'term', 'session'], 'teacher_attendance_sheet_unique');
                }
            });
        }

        if (Schema::hasTable('premium_devices')) {
            Schema::table('premium_devices', function (Blueprint $table) {
                if (Schema::hasColumn('premium_devices', 'tenant_id')) {
                    $table->dropUnique('premium_devices_tenant_device_unique');
                    $table->unique('device_id');
                }
            });
        }

        if (Schema::hasTable('cbt_exams')) {
            Schema::table('cbt_exams', function (Blueprint $table) {
                if (Schema::hasColumn('cbt_exams', 'tenant_id')) {
                    $table->dropUnique('cbt_exams_tenant_access_code_unique');
                    $table->unique('access_code');
                }
            });
        }

        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                if (Schema::hasColumn('transactions', 'tenant_id')) {
                    $table->dropUnique('transactions_tenant_receipt_unique');
                    $table->unique('receipt_number');
                }
            });
        }

        if (Schema::hasTable('custom_fields')) {
            Schema::table('custom_fields', function (Blueprint $table) {
                if (Schema::hasColumn('custom_fields', 'tenant_id')) {
                    $table->dropUnique('custom_fields_tenant_name_unique');
                    $table->unique('name');
                }
            });
        }

        if (Schema::hasTable('certificates')) {
            Schema::table('certificates', function (Blueprint $table) {
                if (Schema::hasColumn('certificates', 'tenant_id')) {
                    $table->dropUnique('certificates_tenant_serial_unique');
                    $table->unique('serial_number');
                }
            });
        }

        if (Schema::hasTable('academic_sessions')) {
            Schema::table('academic_sessions', function (Blueprint $table) {
                if (Schema::hasColumn('academic_sessions', 'tenant_id')) {
                    $table->dropUnique('academic_sessions_tenant_name_unique');
                    $table->unique('name');
                }
            });
        }

        if (Schema::hasTable('subjects')) {
            Schema::table('subjects', function (Blueprint $table) {
                if (Schema::hasColumn('subjects', 'tenant_id')) {
                    $table->dropUnique('subjects_tenant_code_unique');
                    $table->unique('code');
                }
            });
        }

        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                if (Schema::hasColumn('students', 'tenant_id')) {
                    $table->dropUnique('students_tenant_admission_unique');
                    $table->unique('admission_number');
                }
            });
        }

        if (Schema::hasTable('classes')) {
            Schema::table('classes', function (Blueprint $table) {
                if (Schema::hasColumn('classes', 'tenant_id')) {
                    $table->dropUnique('classes_tenant_name_unique');
                    $table->unique('name');
                }
            });
        }

        // Drop tenant_id from all tables.
        $tables = [
            'academic_sessions',
            'academic_terms',
            'announcements',
            'attendance_marks',
            'attendance_sheets',
            'audit_logs',
            'cbt_answers',
            'cbt_attempts',
            'cbt_exams',
            'cbt_options',
            'cbt_questions',
            'certificates',
            'classes',
            'class_subject',
            'conversations',
            'conversation_user',
            'custom_fields',
            'fee_structures',
            'homework',
            'homework_submissions',
            'in_app_notifications',
            'messages',
            'parent_student',
            'promotions',
            'premium_devices',
            'premium_device_removals',
            'result_publications',
            'scores',
            'score_submissions',
            'sections',
            'school_events',
            'student_notifications',
            'students',
            'student_subject_overrides',
            'subject_allocations',
            'subjects',
            'teacher_attendance_marks',
            'teacher_attendance_sheets',
            'timetable_entries',
            'transactions',
            'weekly_data_collections',
            'whatsapp_logs',
        ];

        foreach ($tables as $tableName) {
            if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'tenant_id')) {
                continue;
            }

            $indexName = "{$tableName}_tenant_id_index";
            $fkName = "{$tableName}_tenant_id_foreign";

            if ($this->foreignKeyExists($tableName, $fkName)) {
                Schema::table($tableName, function (Blueprint $table) use ($fkName) {
                    $table->dropForeign($fkName);
                });
            }

            if ($this->indexExists($tableName, $indexName)) {
                Schema::table($tableName, function (Blueprint $table) use ($indexName) {
                    $table->dropIndex($indexName);
                });
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('tenant_id');
            });
        }
    }
};
