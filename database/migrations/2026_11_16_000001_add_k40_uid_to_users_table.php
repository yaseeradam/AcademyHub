<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'k40_uid')) {
            Schema::table('users', function (Blueprint $table) {
                // Hardware biometric UID assigned on the ZKTeco K40 device for this staff member.
                // Reserved range for staff: 1 to 199 (students start at 250).
                $table->unsignedInteger('k40_uid')->nullable()->after('email');
                $table->index('k40_uid', 'users_k40_uid_index');
            });
        }

        // Migrate existing custom_fields->k40_uid and resolve collisions
        $users = DB::table('users')
            ->whereIn('role', ['teacher', 'admin', 'bursar'])
            ->get();

        $studentUids = DB::table('students')
            ->whereNotNull('k40_uid')
            ->pluck('k40_uid')
            ->map(fn($v) => (int) $v)
            ->toArray();

        $assignedUids = [];

        // First pass: migrate valid, existing custom_fields->k40_uid
        foreach ($users as $user) {
            $custom = json_decode($user->custom_fields ?? '[]', true) ?: [];
            $existingUid = isset($custom['k40_uid']) && is_numeric($custom['k40_uid']) ? (int) $custom['k40_uid'] : null;

            if ($existingUid !== null) {
                // Resolve specific collision: User 2 (admin) vs User 3 (teacher) both using 11
                if ($existingUid === 11 && $user->id === 2) {
                    $existingUid = 100; // Administrator gets 100, Teacher gets 11
                }

                if (!in_array($existingUid, $assignedUids) && !in_array($existingUid, $studentUids)) {
                    $assignedUids[] = $existingUid;
                    $custom['k40_uid'] = $existingUid;

                    DB::table('users')->where('id', $user->id)->update([
                        'k40_uid'       => $existingUid,
                        'custom_fields' => json_encode($custom),
                    ]);
                }
            }
        }

        // Second pass: auto-assign sequential, conflict-free IDs to unassigned teachers
        $nextFreeId = 1;
        foreach ($users as $user) {
            $currentK40 = DB::table('users')->where('id', $user->id)->value('k40_uid');
            if ($currentK40 === null) {
                while (in_array($nextFreeId, $assignedUids) || in_array($nextFreeId, $studentUids)) {
                    $nextFreeId++;
                }

                $assignedUids[] = $nextFreeId;
                $custom = json_decode($user->custom_fields ?? '[]', true) ?: [];
                $custom['k40_uid'] = $nextFreeId;

                DB::table('users')->where('id', $user->id)->update([
                    'k40_uid'       => $nextFreeId,
                    'custom_fields' => json_encode($custom),
                ]);

                $nextFreeId++;
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'k40_uid')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_k40_uid_index');
                $table->dropColumn('k40_uid');
            });
        }
    }
};
