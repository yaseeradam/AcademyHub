<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MakeSuperAdmin extends Command
{
    protected $signature   = 'superadmin:make {email? : Email of the user to promote}
                                               {--create : Create a new super admin account}
                                               {--list   : List all current super admins}';

    protected $description = 'Manage super admin accounts for the Dev Dashboard';

    public function handle(): int
    {
        if ($this->option('list')) {
            return $this->listSuperAdmins();
        }

        if ($this->option('create')) {
            return $this->createSuperAdmin();
        }

        $email = $this->argument('email') ?? $this->ask('Enter the email address to promote');

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("No user found with email: {$email}");
            return 1;
        }

        if ($user->is_super_admin) {
            $this->warn("User {$user->name} ({$email}) is already a super admin.");
            return 0;
        }

        $user->update([
            'is_super_admin' => true,
            'tenant_id' => null,
        ]);
        $this->info("✅ {$user->name} ({$email}) has been promoted to Super Admin (tenant_id cleared).");
        $this->info("   They can now access /superadmin after logging in.");

        return 0;
    }

    private function listSuperAdmins(): int
    {
        $admins = User::where('is_super_admin', true)->get(['id', 'name', 'email', 'role']);

        if ($admins->isEmpty()) {
            $this->warn('No super admins found. Run: php artisan superadmin:make {email}');
            return 0;
        }

        $this->table(['ID', 'Name', 'Email', 'Role'], $admins->toArray());
        return 0;
    }

    private function createSuperAdmin(): int
    {
        $name     = $this->ask('Full name');
        $email    = $this->ask('Email address');
        $password = $this->secret('Password (min 8 chars)');

        $validator = Validator::make(compact('name', 'email', 'password'), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return 1;
        }

        $user = User::create([
            'name'           => $name,
            'email'          => $email,
            'password'       => Hash::make($password),
            'role'           => 'admin',
            'is_active'      => true,
            'is_super_admin' => true,
            'tenant_id'      => null,
        ]);

        $this->info("✅ Super admin account created for {$user->name} ({$email})");
        $this->info("   Login at /login and visit /superadmin");

        return 0;
    }
}
