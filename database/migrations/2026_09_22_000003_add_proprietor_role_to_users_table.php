<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // In MySQL, alter ENUM column in-place without dropping or losing data
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'bursar', 'teacher', 'parent', 'proprietor') NOT NULL DEFAULT 'teacher'");
        } else {
            // In SQLite (tests), drop and recreate to update the CHECK constraint while preserving existing roles
            $existingRoles = DB::table('users')->pluck('role', 'id');
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
            Schema::table('users', function (Blueprint $table) {
                $table->string('role', 20)->default('teacher')->after('password');
            });
            foreach ($existingRoles as $id => $role) {
                DB::table('users')->where('id', $id)->update(['role' => $role]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'bursar', 'teacher', 'parent') NOT NULL DEFAULT 'teacher'");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['admin', 'bursar', 'teacher', 'parent'])
                    ->default('teacher')
                    ->after('password');
            });
        }
    }
};
