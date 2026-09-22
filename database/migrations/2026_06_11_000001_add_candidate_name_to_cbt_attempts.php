<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cbt_attempts', function (Blueprint $table) {
            $table->string('candidate_name', 200)->nullable()->after('student_id');
        });

        // Drop foreign key, make student_id nullable, and re-add foreign key (skip on SQLite)
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            Schema::table('cbt_attempts', function (Blueprint $table) {
                $table->dropForeign('cbt_attempts_student_id_foreign');
            });
        }

        Schema::table('cbt_attempts', function (Blueprint $table) {
            $table->unsignedBigInteger('student_id')->nullable()->change();
        });

        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            Schema::table('cbt_attempts', function (Blueprint $table) {
                $table->foreign('student_id')
                    ->references('id')
                    ->on('students')
                    ->cascadeOnDelete();
            });
        }

        // Backfill candidate_name from existing APT- student records
        $attempts = \Illuminate\Support\Facades\DB::table('cbt_attempts as a')
            ->join('students as s', 'a.student_id', '=', 's.id')
            ->where('s.admission_number', 'like', 'APT-%')
            ->select(['a.id', 's.first_name', 's.last_name'])
            ->get();

        foreach ($attempts as $attempt) {
            \Illuminate\Support\Facades\DB::table('cbt_attempts')
                ->where('id', $attempt->id)
                ->update(['candidate_name' => trim($attempt->first_name . ' ' . $attempt->last_name)]);
        }
    }

    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            Schema::table('cbt_attempts', function (Blueprint $table) {
                $table->dropForeign('cbt_attempts_student_id_foreign');
            });
        }

        Schema::table('cbt_attempts', function (Blueprint $table) {
            $table->unsignedBigInteger('student_id')->nullable(false)->change();
        });

        Schema::table('cbt_attempts', function (Blueprint $table) {
            if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
                $table->foreign('student_id')
                    ->references('id')
                    ->on('students')
                    ->cascadeOnDelete();
            }
            
            $table->dropColumn('candidate_name');
        });
    }
};
