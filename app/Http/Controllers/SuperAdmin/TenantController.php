<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantDataAdopter;
use App\Support\TenantProvisioner;
use App\Support\TenantSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class TenantController extends Controller
{
    // ── Password Guard ────────────────────────────────────────────────────────

    private function verifyPassword(Request $request): bool
    {
        return Hash::check($request->input('sa_password', ''), Auth::user()->password);
    }

    private function denyPassword()
    {
        return back()->with('error', 'Incorrect password. Action was not performed.');
    }
    public function index()
    {
        $tenants = Tenant::latest()->paginate(20);
        return view('superadmin.tenants.index', compact('tenants'));
    }

    public function create()
    {
        $isFirstTenant = Tenant::query()->count() === 0;
        $legacyDataExists = $isFirstTenant && $this->hasLegacySingleSchoolData();

        return view('superadmin.tenants.create', compact('isFirstTenant', 'legacyDataExists'));
    }

    public function store(Request $request, TenantProvisioner $provisioner, TenantDataAdopter $adopter)
    {
        $wasFirstTenant = Tenant::query()->count() === 0;

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'slug'          => ['nullable', 'string', 'max:80', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:tenants,slug'],
            'domain'        => ['nullable', 'string', 'max:255', 'unique:tenants,domain'],
            'plan'          => ['required', 'string', 'in:free,pro,enterprise'],
            'status'        => ['required', 'string', 'in:active,suspended,pending'],
            'max_students'  => ['required', 'integer', 'min:1'],
            'max_teachers'  => ['required', 'integer', 'min:1'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'adopt_existing_data' => ['nullable', 'boolean'],

            // Optional admin account
            'create_admin'              => ['nullable'],
            'admin_name'                => ['nullable', 'required_if:create_admin,1', 'string', 'max:255'],
            'admin_email'               => ['nullable', 'required_if:create_admin,1', 'email', 'max:255', 'unique:users,email'],
            'admin_password'            => ['nullable', 'required_if:create_admin,1', 'confirmed', Password::min(8)],
        ]);

        // Reserved slugs — these match physical directories or system routes that would
        // cause Apache/Nginx to serve the directory instead of routing to Laravel.
        $reserved = [
            'superadmin', 'api', 'login', 'logout', 'register', 'password',
            'students', 'teachers', 'certificates', 'uploads', 'storage',
            'build', 'images', 'bgs', 'public', 'admin', 'dashboard',
        ];

        $chosenSlug = trim($data['slug'] ?? '');

        if ($chosenSlug !== '') {
            if (in_array(strtolower($chosenSlug), $reserved, true)) {
                return back()->withInput()
                    ->withErrors(['slug' => "The slug '{$chosenSlug}' is reserved and cannot be used."]);
            }
            $data['slug'] = $chosenSlug;
        } else {
            // Auto-generate a slug from the name, appending a short random suffix for uniqueness
            $base = Str::slug($data['name']);
            $slug = $base;
            // Keep trying until we get a unique, non-reserved slug
            while (in_array($slug, $reserved, true) || \App\Models\Tenant::where('slug', $slug)->exists()) {
                $slug = $base . '-' . strtolower(Str::random(5));
            }
            $data['slug'] = $slug;
        }

        // Remove admin fields from tenant data
        $adminData   = null;
        $createAdmin = $request->filled('create_admin');

        if ($createAdmin) {
            $adminData = [
                'name'     => $data['admin_name'],
                'email'    => $data['admin_email'],
                'password' => Hash::make($data['admin_password']),
                'role'     => 'admin',
                'is_active'=> true,
            ];
        }

        unset($data['create_admin'], $data['admin_name'], $data['admin_email'], $data['admin_password'], $data['admin_password_confirmation']);

        $tenant = null;

        $adopt = $wasFirstTenant && $request->boolean('adopt_existing_data');

        DB::transaction(function () use ($data, $adminData, $provisioner, $adopter, $adopt, &$tenant) {
            $tenant = Tenant::create($data);

            // Single-school upgrade: adopt existing data into the first tenant BEFORE provisioning defaults
            // to preserve original IDs and relationships.
            if ($adopt) {
                $adopter->adoptNullTenantData($tenant->id);
            }

            // Create the admin user and associate with the tenant
            if ($adminData) {
                $adminData['tenant_id'] = $tenant->id;
                User::create($adminData);
            }

            // Ensure the new tenant has baseline data & a settings file so it can log in immediately.
            $provisioner->provision($tenant);
        });

        $message = 'School instance created successfully.';
        if ($adminData) {
            $message .= ' Admin account created for ' . $adminData['email'] . '.';
        }

        $mainHost = parse_url(config('app.url'), PHP_URL_HOST);
        $accessHost = $tenant->domain ?: ($mainHost ? ($tenant->slug.'.'.$mainHost) : $tenant->slug);
        $message .= ' Access it at: ' . $accessHost;

        // Auto-add to Windows hosts file for local development
        if (app()->environment('local', 'development') || str_contains(config('app.url'), '.test')) {
            $this->addToHostsFile($accessHost);
            $message .= ' (Added to hosts file automatically)';
        }

        return redirect()->route('superadmin.tenants.index')
            ->with('status', $message);
    }

    private function addToHostsFile(string $host): void
    {
        try {
            $hostsFile = 'C:\Windows\System32\drivers\etc\hosts';
            $contents  = file_get_contents($hostsFile);

            if (str_contains($contents, $host)) {
                return;
            }

            // Use \r\n for Windows hosts file compatibility
            $entry = "\r\n127.0.0.1      {$host} #laragon magic!";
            file_put_contents($hostsFile, rtrim($contents) . $entry . "\r\n");
        } catch (\Throwable) {
            // Silently fail
        }
    }

    // ── Reset Admin Password ───────────────────────────────────────────────

    public function resetAdminPassword(Request $request, Tenant $tenant)
    {
        if (! $this->verifyPassword($request)) return $this->denyPassword();

        $data = $request->validate([
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $admin = User::where('tenant_id', $tenant->id)
            ->where('role', 'admin')
            ->where('is_active', true)
            ->first();

        if (! $admin) {
            return back()->with('error', 'No active admin found for this school.');
        }

        $admin->forceFill([
            'password'                => Hash::make($data['new_password']),
            'password_reset_required' => true,
        ])->save();

        return back()->with('status', "Admin password for {$tenant->name} has been reset. They will be prompted to change it on next login.");
    }

    // ── Subscription ───────────────────────────────────────────────────────

    public function updateSubscription(Request $request, Tenant $tenant)
    {
        if (! $this->verifyPassword($request)) return $this->denyPassword();

        $data = $request->validate([
            'plan'             => ['required', 'in:free,pro,enterprise'],
            'status'           => ['required', 'in:active,suspended,pending'],
            'subscription_due_date' => ['nullable', 'date'],
            'max_students'     => ['required', 'integer', 'min:1'],
            'max_teachers'     => ['required', 'integer', 'min:1'],
        ]);

        $tenant->update([
            'plan'         => $data['plan'],
            'status'       => $data['status'],
            'max_students' => $data['max_students'],
            'max_teachers' => $data['max_teachers'],
        ]);

        // Write subscription_due_date into tenant settings file
        if (! empty($data['subscription_due_date'])) {
            $path     = storage_path('app/myacademy/tenants/' . $tenant->id . '/settings.json');
            $settings = File::exists($path) ? (json_decode(File::get($path), true) ?: []) : [];
            $settings['subscription_due_date'] = $data['subscription_due_date'];
            File::put($path, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            Cache::forget(TenantSettings::settingsCacheKey($tenant));
        }

        return back()->with('status', "Subscription updated for {$tenant->name}.");
    }

    // ── Feature Flags ──────────────────────────────────────────────────────

    public function updateFeatureFlags(Request $request, Tenant $tenant)
    {
        if (! $this->verifyPassword($request)) return $this->denyPassword();
        $flags = [
            'feature_cbt'           => $request->boolean('feature_cbt'),
            'feature_whatsapp'      => $request->boolean('feature_whatsapp'),
            'feature_parent_portal' => $request->boolean('feature_parent_portal'),
            'feature_ai'            => $request->boolean('feature_ai'),
            'feature_analytics'     => $request->boolean('feature_analytics'),
            'feature_billing'       => $request->boolean('feature_billing'),
        ];

        $path     = storage_path('app/myacademy/tenants/' . $tenant->id . '/settings.json');
        $settings = File::exists($path) ? (json_decode(File::get($path), true) ?: []) : [];
        $settings = array_merge($settings, $flags);
        File::put($path, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        Cache::forget(TenantSettings::settingsCacheKey($tenant));

        return back()->with('status', "Feature flags updated for {$tenant->name}.");
    }

    // ── Broadcast ──────────────────────────────────────────────────────────

    public function broadcast(Request $request)
    {
        if (! $this->verifyPassword($request)) return $this->denyPassword();
        $data = $request->validate([
            'message'  => ['required', 'string', 'max:1000'],
            'target'   => ['required', 'in:all,active,suspended'],
        ]);

        $query = Tenant::query();
        if ($data['target'] !== 'all') {
            $query->where('status', $data['target']);
        }

        $tenants = $query->get();
        $count   = 0;

        foreach ($tenants as $tenant) {
            // Write broadcast to a notices file each tenant's admin will see on login
            $path    = storage_path('app/myacademy/tenants/' . $tenant->id . '/notices.json');
            $notices = File::exists($path) ? (json_decode(File::get($path), true) ?: []) : [];
            array_unshift($notices, [
                'id'        => Str::uuid(),
                'message'   => $data['message'],
                'from'      => 'System',
                'created_at'=> now()->toDateTimeString(),
                'read'      => false,
            ]);
            // Keep only last 10 notices
            $notices = array_slice($notices, 0, 10);
            File::ensureDirectoryExists(dirname($path));
            File::put($path, json_encode($notices, JSON_PRETTY_PRINT));
            $count++;
        }

        return back()->with('status', "Broadcast sent to {$count} school(s).");
    }

    // ── Reset School Data ──────────────────────────────────────────────────

    public function resetData(Request $request, Tenant $tenant)
    {
        if (! $this->verifyPassword($request)) return $this->denyPassword();
        $tables = [
            'students', 'classes', 'sections', 'subjects', 'scores',
            'transactions', 'homework', 'homework_submissions',
            'attendance_sheets', 'attendance_marks', 'cbt_exams',
            'cbt_attempts', 'cbt_questions', 'cbt_answers',
            'academic_sessions', 'academic_terms', 'subject_allocations',
        ];

        DB::transaction(function () use ($tenant, $tables) {
            foreach ($tables as $table) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                    DB::table($table)->where('tenant_id', $tenant->id)->delete();
                }
            }
            // Also wipe tenant users except admins
            User::where('tenant_id', $tenant->id)
                ->where('role', '!=', 'admin')
                ->delete();
        });

        // Re-provision defaults
        app(TenantProvisioner::class)->provision($tenant);

        Cache::forget(TenantSettings::settingsCacheKey($tenant));

        return back()->with('status', "{$tenant->name} data has been reset and re-provisioned.");
    }

    // ── Health Check ───────────────────────────────────────────────────────

    public function health(Tenant $tenant)
    {
        $health = [
            'has_admin'       => User::where('tenant_id', $tenant->id)->where('role', 'admin')->where('is_active', true)->exists(),
            'has_students'    => DB::table('students')->where('tenant_id', $tenant->id)->exists(),
            'has_teachers'    => User::where('tenant_id', $tenant->id)->where('role', 'teacher')->exists(),
            'has_classes'     => DB::table('classes')->where('tenant_id', $tenant->id)->exists(),
            'has_active_term' => DB::table('academic_terms')->where('tenant_id', $tenant->id)->where('is_active', true)->exists(),
            'student_count'   => DB::table('students')->where('tenant_id', $tenant->id)->count(),
            'teacher_count'   => User::where('tenant_id', $tenant->id)->where('role', 'teacher')->count(),
            'last_login'      => User::where('tenant_id', $tenant->id)->max('last_login_at'),
        ];

        return response()->json($health);
    }

    // ── Auto-Suspend Expired Tenants ─────────────────────────────────────────

    public function autoSuspend(Request $request)
    {
        if (! $this->verifyPassword($request)) return $this->denyPassword();
        $suspended = 0;
        foreach (Tenant::where('status', 'active')->get() as $tenant) {
            $path = storage_path('app/myacademy/tenants/' . $tenant->id . '/settings.json');
            if (! File::exists($path)) continue;
            $s   = json_decode(File::get($path), true) ?: [];
            if (empty($s['subscription_due_date'])) continue;
            $due = \Carbon\Carbon::parse($s['subscription_due_date']);
            if ($due->isPast() && $due->diffInDays(now()) > ($tenant->grace_days ?? 7)) {
                $tenant->update(['status' => 'suspended']);
                $suspended++;
            }
        }
        return back()->with('status', "Auto-suspend complete. {$suspended} school(s) suspended.");
    }

    // ── Force Password Reset ──────────────────────────────────────────────────

    public function forcePasswordReset(Request $request, Tenant $tenant)
    {
        if (! $this->verifyPassword($request)) return $this->denyPassword();
        User::where('tenant_id', $tenant->id)
            ->update(['password_reset_required' => true]);
        return back()->with('status', "All users of {$tenant->name} must reset their password on next login.");
    }

    // ── Backup ────────────────────────────────────────────────────────────────

    public function triggerBackup(Request $request, Tenant $tenant)
    {
        if (! $this->verifyPassword($request)) return $this->denyPassword();
        // Store a backup request flag — the school's admin will see it and can trigger
        $path     = storage_path('app/myacademy/tenants/' . $tenant->id . '/settings.json');
        $settings = File::exists($path) ? (json_decode(File::get($path), true) ?: []) : [];
        $settings['backup_requested_at'] = now()->toDateTimeString();
        $settings['backup_requested_by'] = 'superadmin';
        File::put($path, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        Cache::forget(TenantSettings::settingsCacheKey($tenant));
        return back()->with('status', "Backup requested for {$tenant->name}.");
    }

    // ── Clone School ──────────────────────────────────────────────────────────

    public function clone(Request $request, Tenant $source, TenantProvisioner $provisioner)
    {
        if (! $this->verifyPassword($request)) return $this->denyPassword();
        $clone = DB::transaction(function () use ($source, $provisioner) {
            $new = Tenant::create([
                'name'          => $source->name . ' (Copy)',
                'slug'          => Str::slug($source->name . '-copy') . '-' . strtolower(Str::random(4)),
                'plan'          => $source->plan,
                'status'        => 'pending',
                'max_students'  => $source->max_students,
                'max_teachers'  => $source->max_teachers,
                'contact_email' => $source->contact_email,
                'contact_phone' => $source->contact_phone,
            ]);

            // Copy settings file
            $srcPath = storage_path('app/myacademy/tenants/' . $source->id . '/settings.json');
            $dstPath = storage_path('app/myacademy/tenants/' . $new->id . '/settings.json');
            File::ensureDirectoryExists(dirname($dstPath));
            if (File::exists($srcPath)) {
                $s = json_decode(File::get($srcPath), true) ?: [];
                $s['school_name'] = $new->name;
                File::put($dstPath, json_encode($s, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            }

            // Copy classes, sections, subjects
            $classMap = [];
            foreach (\App\Models\SchoolClass::where('tenant_id', $source->id)->get() as $class) {
                $newClass = \App\Models\SchoolClass::create(['tenant_id' => $new->id, 'name' => $class->name, 'level' => $class->level]);
                $classMap[$class->id] = $newClass->id;
                foreach (\App\Models\Section::where('class_id', $class->id)->get() as $section) {
                    \App\Models\Section::create(['tenant_id' => $new->id, 'class_id' => $newClass->id, 'name' => $section->name]);
                }
            }
            foreach (\App\Models\Subject::where('tenant_id', $source->id)->get() as $subject) {
                \App\Models\Subject::create(['tenant_id' => $new->id, 'name' => $subject->name, 'code' => $subject->code]);
            }

            $provisioner->provision($new);
            return $new;
        });

        $mainHost   = parse_url(config('app.url'), PHP_URL_HOST);
        $accessHost = $clone->slug . '.' . $mainHost;
        $this->addToHostsFile($accessHost);

        return redirect()->route('superadmin.tenants.index')
            ->with('status', "Cloned as {$clone->name}. Access it at: {$accessHost}");
    }

    // ── Global User Search ────────────────────────────────────────────────────

    public function searchUsers(\Illuminate\Http\Request $request)
    {
        $q       = trim($request->input('q', ''));
        $results = collect();

        if (strlen($q) >= 2) {
            $results = User::with('tenant:id,name,slug')
                ->where('is_super_admin', false)
                ->where(fn($query) => $query
                    ->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%")
                )
                ->limit(20)
                ->get(['id', 'name', 'email', 'role', 'tenant_id', 'is_active']);
        }

        return view('superadmin.users.search', compact('results', 'q'));
    }

    private function hasLegacySingleSchoolData(): bool
    {
        try {
            foreach (['students', 'classes', 'subjects', 'academic_sessions'] as $table) {
                if (! Schema::hasTable($table)) {
                    continue;
                }

                if (DB::table($table)->whereNull('tenant_id')->exists()) {
                    return true;
                }
            }
        } catch (\Throwable) {
            return false;
        }

        return false;
    }

    public function edit(Tenant $tenant)
    {
        return view('superadmin.tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $reserved = [
            'superadmin', 'api', 'login', 'logout', 'register', 'password',
            'students', 'teachers', 'certificates', 'uploads', 'storage',
            'build', 'images', 'bgs', 'public', 'admin', 'dashboard',
        ];

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'slug'          => ['required', 'string', 'max:80', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:tenants,slug,' . $tenant->id],
            'domain'        => ['nullable', 'string', 'max:255', 'unique:tenants,domain,' . $tenant->id],
            'plan'          => ['required', 'string', 'in:free,pro,enterprise'],
            'status'        => ['required', 'string', 'in:active,suspended,pending'],
            'max_students'  => ['required', 'integer', 'min:1'],
            'max_teachers'  => ['required', 'integer', 'min:1'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
        ]);

        if (in_array(strtolower($data['slug']), $reserved, true)) {
            return back()->withInput()
                ->withErrors(['slug' => "The slug '{$data['slug']}' is reserved and cannot be used."]);
        }

        // If slug changed, update hosts file entry
        if ($data['slug'] !== $tenant->slug) {
            $mainHost = parse_url(config('app.url'), PHP_URL_HOST);
            $oldHost  = $tenant->domain ?: ($tenant->slug . '.' . $mainHost);
            $newHost  = $tenant->domain ?: ($data['slug'] . '.' . $mainHost);
            $this->removeFromHostsFile($oldHost);
            if (app()->environment('local', 'development') || str_contains(config('app.url'), '.test')) {
                $this->addToHostsFile($newHost);
            }
        }

        $tenant->update($data);

        return redirect()->route('superadmin.tenants.index')
            ->with('status', 'School updated successfully.');
    }

    public function destroy(Request $request, Tenant $tenant)
    {
        if (! $this->verifyPassword($request)) return $this->denyPassword();
        $mainHost   = parse_url(config('app.url'), PHP_URL_HOST);
        $accessHost = $tenant->domain ?: ($mainHost ? ($tenant->slug.'.'.$mainHost) : $tenant->slug);

        $tenant->delete();

        // Remove from hosts file
        $this->removeFromHostsFile($accessHost);

        return redirect()->route('superadmin.tenants.index')
            ->with('status', 'School instance deleted.');
    }

    private function removeFromHostsFile(string $host): void
    {
        try {
            $hostsFile = 'C:\Windows\System32\drivers\etc\hosts';
            $contents  = file_get_contents($hostsFile);
            $lines     = explode(PHP_EOL, $contents);
            $filtered  = array_filter($lines, fn($line) => !str_contains($line, $host));
            file_put_contents($hostsFile, implode(PHP_EOL, $filtered));
        } catch (\Throwable) {
            // Silently fail
        }
    }
}
