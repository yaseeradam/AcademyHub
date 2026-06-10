<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Student;
use App\Models\MarketplaceComponent;
use App\Models\TenantPluginBill;
use App\Models\AuditLog;
use App\Support\TenantDataAdopter;
use App\Support\TenantProvisioner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class TenantController extends Controller
{
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

        $data['slug'] = Str::slug($data['name']) . '-' . strtolower(Str::random(5));

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

            // Dynamically sync plugins based on selected plan
            $this->syncTenantPluginsByPlan($tenant, $tenant->plan);
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
        $components = \App\Models\MarketplaceComponent::orderBy('name')->get();
        
        // Eagerly resolve tenant admin users
        $admins = \App\Models\User::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('role', 'admin')
            ->get();
            
        // Tenant Stats counts
        $studentCount = \App\Models\Student::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count();
        $teacherCount = \App\Models\User::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('role', 'teacher')->count();
        $parentCount = \App\Models\User::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('role', 'parent')->count();
        $adminCount = \App\Models\User::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('role', 'admin')->count();
        
        // Target school classes
        $classes = \App\Models\SchoolClass::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->orderBy('level')
            ->orderBy('name')
            ->get();
            
        // Billing Ledger
        $bills = \App\Models\TenantPluginBill::where('tenant_id', $tenant->id)
            ->with('marketplaceComponent')
            ->latest()
            ->get();

        return view('superadmin.tenants.edit', compact(
            'tenant', 'components', 'admins', 'classes', 'bills',
            'studentCount', 'teacherCount', 'parentCount', 'adminCount'
        ));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'slug'          => ['required', 'string', 'max:255', 'alpha_dash', 'unique:tenants,slug,' . $tenant->id],
            'domain'        => ['nullable', 'string', 'max:255', 'unique:tenants,domain,' . $tenant->id],
            'plan'          => ['required', 'string', 'in:free,pro,enterprise'],
            'status'        => ['required', 'string', 'in:active,suspended,pending'],
            'max_students'  => ['required', 'integer', 'min:1'],
            'max_teachers'  => ['required', 'integer', 'min:1'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'expires_at'    => ['nullable', 'date'],
        ]);

        $oldMainHost = parse_url(config('app.url'), PHP_URL_HOST);
        $oldAccessHost = $tenant->domain ?: ($oldMainHost ? ($tenant->slug.'.'.$oldMainHost) : $tenant->slug);

        $tenant->update($data);

        $newMainHost = parse_url(config('app.url'), PHP_URL_HOST);
        $newAccessHost = $tenant->domain ?: ($newMainHost ? ($tenant->slug.'.'.$newMainHost) : $tenant->slug);

        if ($oldAccessHost !== $newAccessHost) {
            if (app()->environment('local', 'development') || str_contains(config('app.url'), '.test')) {
                $this->removeFromHostsFile($oldAccessHost);
                $this->addToHostsFile($newAccessHost);
            }
        }

        // Keep plugins synced with the plan on update
        $this->syncTenantPluginsByPlan($tenant, $tenant->plan);

        return redirect()->route('superadmin.tenants.edit', $tenant)
            ->with('status', 'School instance updated successfully.');
    }

    public function updateAdmin(Request $request, Tenant $tenant, User $admin)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $admin->id],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);

        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $admin->update($updateData);

        return redirect()->route('superadmin.tenants.edit', $tenant)
            ->with('status', 'Admin user updated successfully.');
    }

    // DNS diagnostics
    public function checkDns(Tenant $tenant)
    {
        $mainHost = parse_url(config('app.url'), PHP_URL_HOST);
        $domain = $tenant->domain ?: ($tenant->slug . '.' . ($mainHost ?: 'academyhub.local'));
        
        $dnsResults = [];
        $pingResult = 'Offline / No connection';
        $sslResult = 'Invalid / No SSL Certificate';
        
        // 1. DNS A record check
        if (function_exists('dns_get_record')) {
            $records = @dns_get_record($domain, DNS_A);
            if (!empty($records)) {
                foreach ($records as $r) {
                    $dnsResults[] = $r['ip'];
                }
            }
        }
        if (empty($dnsResults)) {
            $ip = @gethostbyname($domain);
            if ($ip && $ip !== $domain) {
                $dnsResults[] = $ip;
            }
        }
        
        // 2. Ping check
        $startTime = microtime(true);
        $context = stream_context_create([
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
            'http' => ['timeout' => 3.0, 'ignore_errors' => true]
        ]);
        
        $url = 'https://' . $domain;
        $response = @file_get_contents($url, false, $context);
        if ($response !== false) {
            $pingResult = 'Online (' . round((microtime(true) - $startTime) * 1000) . 'ms)';
            $sslResult = 'Valid SSL Active';
        } else {
            // Check HTTP
            $responseHttp = @file_get_contents('http://' . $domain, false, $context);
            if ($responseHttp !== false) {
                $pingResult = 'Online (HTTP Only)';
                $sslResult = 'No SSL / Insecure';
            }
        }
        
        return response()->json([
            'domain' => $domain,
            'dns'    => !empty($dnsResults) ? implode(', ', $dnsResults) : 'No A Records Resolved',
            'ping'   => $pingResult,
            'ssl'    => $sslResult,
        ]);
    }

    // Impersonate Tenant Admin
    public function impersonate(Tenant $tenant)
    {
        $admin = User::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('role', 'admin')
            ->first();
            
        if (!$admin) {
            return redirect()->back()->with('error', 'No admin user found for this school.');
        }
        
        // Store superadmin ID in session so they can stop impersonating later
        $superadminId = auth()->id();
        session(['impersonator_id' => $superadminId]);
        
        // Log in as the tenant admin
        auth()->login($admin);
        
        // Set context
        app()->instance('currentTenant', $tenant);
        
        // Redirect to school dashboard
        return redirect()->route('dashboard')->with('status', 'Impersonating ' . $admin->name);
    }

    // Modular Feature Flags & resource quotas
    public function saveFlags(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'feature_flags'     => 'nullable|array',
            'max_disk_usage_mb' => 'required|integer|min:50|max:100000',
        ]);
        
        $tenant->update([
            'feature_flags'     => $request->input('feature_flags', []),
            'max_disk_usage_mb' => $data['max_disk_usage_mb'],
        ]);
        
        return redirect()->route('superadmin.tenants.edit', $tenant)
            ->with('status', 'Superpower flags and resource quotas updated successfully.');
    }

    // Warning / Announcement banners
    public function saveBroadcast(Request $request, Tenant $tenant)
    {
        $tenant->update([
            'active_broadcast_banner' => $request->input('active_broadcast_banner'),
        ]);
        
        return redirect()->route('superadmin.tenants.edit', $tenant)
            ->with('status', 'Broadcast banner successfully saved.');
    }

    public function updatePluginPricing(Request $request, Tenant $tenant, \App\Models\MarketplaceComponent $component)
    {
        $data = $request->validate([
            'setup_fee'             => 'required|numeric|min:0',
            'usage_fee_per_student' => 'required|numeric|min:0',
            'status'                => 'required|string|in:active,suspended',
        ]);
        
        $tenant->marketplaceComponents()->updateExistingPivot($component->id, [
            'setup_fee'             => $data['setup_fee'],
            'usage_fee_per_student' => $data['usage_fee_per_student'],
            'status'                => $data['status'],
        ]);
        
        return redirect()->route('superadmin.tenants.edit', $tenant)
            ->with('status', 'Plugin configuration override updated successfully.');
    }

    public function activatePlugin(Tenant $tenant, \App\Models\MarketplaceComponent $component)
    {
        $pivot = $tenant->marketplaceComponents()
            ->where('marketplace_component_id', $component->id)
            ->first();

        $setupFee = (float) $component->setup_fee;
        $usageFee = (float) $component->usage_fee_per_student;

        if ($pivot) {
            // Update existing pivot row
            $tenant->marketplaceComponents()->updateExistingPivot($component->id, [
                'installed_at'   => now(),
                'uninstalled_at' => null,
                'status'         => 'active',
            ]);
        } else {
            // Attach a new pivot row
            $tenant->marketplaceComponents()->attach($component->id, [
                'installed_at'             => now(),
                'uninstalled_at'           => null,
                'status'                   => 'active',
                'setup_fee'                => $setupFee,
                'usage_fee_per_student'    => $usageFee,
                'price_paid'               => $setupFee,
                'student_count_at_install' => 0,
                'allowed_class_ids'        => [],
            ]);
        }

        // Increment installs count
        $component->increment('installs');

        // Check if setup bill already exists
        $hasSetupBill = \App\Models\TenantPluginBill::where('tenant_id', $tenant->id)
            ->where('marketplace_component_id', $component->id)
            ->where('bill_type', 'setup')
            ->exists();

        if (!$hasSetupBill && $setupFee > 0) {
            \App\Models\TenantPluginBill::create([
                'tenant_id'                => $tenant->id,
                'marketplace_component_id' => $component->id,
                'bill_type'                => 'setup',
                'term_name'                => null,
                'session_name'             => null,
                'student_count'            => null,
                'setup_fee'                => $setupFee,
                'usage_fee_per_student'    => 0,
                'total_due'                => $setupFee,
                'status'                   => 'paid',
                'paid_at'                  => now(),
            ]);
        }

        // Audit Log
        AuditLog::create([
            'user_id'  => auth()->id() ?? 1,
            'action'   => 'plugin_activated_by_superadmin',
            'model'    => 'MarketplaceComponent',
            'model_id' => $component->id,
            'changes'  => json_encode([
                'slug'      => $component->slug,
                'setup_fee' => $setupFee,
                'usage_fee' => $usageFee,
            ]),
        ]);

        return redirect()->route('superadmin.tenants.edit', $tenant)
            ->with('status', "Plugin '{$component->name}' has been successfully activated.");
    }

    public function deactivatePlugin(Tenant $tenant, \App\Models\MarketplaceComponent $component)
    {
        $tenant->marketplaceComponents()
            ->wherePivot('marketplace_component_id', $component->id)
            ->updateExistingPivot($component->id, [
                'uninstalled_at' => now(),
            ]);

        if ($component->installs > 0) {
            $component->decrement('installs');
        }

        // Audit Log
        AuditLog::create([
            'user_id'  => auth()->id() ?? 1,
            'action'   => 'plugin_deactivated_by_superadmin',
            'model'    => 'MarketplaceComponent',
            'model_id' => $component->id,
            'changes'  => json_encode([
                'slug'           => $component->slug,
                'uninstalled_at' => now()
            ]),
        ]);

        return redirect()->route('superadmin.tenants.edit', $tenant)
            ->with('status', "Plugin '{$component->name}' has been successfully deactivated.");
    }

    // Generate Invoice
    public function generateBill(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'component_id' => 'required|exists:marketplace_components,id',
            'term_name'    => 'required|string|max:50',
            'session_name' => 'required|string|max:50',
        ]);
        
        $component = \App\Models\MarketplaceComponent::findOrFail($data['component_id']);
        
        // Find pivot fields
        $pivot = $tenant->marketplaceComponents()
            ->where('marketplace_component_id', $component->id)
            ->first();
            
        $usageFee = $pivot && $pivot->pivot->usage_fee_per_student !== null 
            ? (float) $pivot->pivot->usage_fee_per_student 
            : (float) $component->usage_fee_per_student;
            
        $allowedClassIds = $pivot ? ($pivot->pivot->allowed_class_ids ?? []) : [];
        
        // Query target student count in those classes
        if (!empty($allowedClassIds)) {
            $studentCount = Student::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->whereIn('class_id', $allowedClassIds)
                ->where('status', 'active')
                ->count();
        } else {
            // Default to all active students
            $studentCount = Student::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('status', 'active')
                ->count();
        }
        
        $totalDue = $studentCount * $usageFee;
        
        \App\Models\TenantPluginBill::create([
            'tenant_id'                => $tenant->id,
            'marketplace_component_id' => $component->id,
            'bill_type'                => 'usage',
            'term_name'                => $data['term_name'],
            'session_name'             => $data['session_name'],
            'student_count'            => $studentCount,
            'setup_fee'                => 0,
            'usage_fee_per_student'    => $usageFee,
            'total_due'                => $totalDue,
            'status'                   => 'unpaid',
        ]);
        
        return redirect()->route('superadmin.tenants.edit', $tenant)
            ->with('status', 'Termly usage bill successfully generated and added to ledger.');
    }

    public function payBill(Tenant $tenant, \App\Models\TenantPluginBill $bill)
    {
        $bill->update([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);
        return redirect()->route('superadmin.tenants.edit', $tenant)
            ->with('status', 'Bill marked as Paid successfully.');
    }
    
    public function voidBill(Tenant $tenant, \App\Models\TenantPluginBill $bill)
    {
        $bill->update([
            'status' => 'void',
        ]);
        return redirect()->route('superadmin.tenants.edit', $tenant)
            ->with('status', 'Bill marked as Void successfully.');
    }

    public function destroy(Tenant $tenant)
    {
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

    public function exportBackup(Tenant $tenant)
    {
        try {
            $backup = [
                'version' => '1.0',
                'tenant_id' => $tenant->id,
                'school_slug' => $tenant->slug,
                'exported_at' => now()->toDateTimeString(),
                'tenant' => DB::table('tenants')->where('id', $tenant->id)->first(),
                'tables' => [],
                'pivots' => []
            ];

            // Get all tables dynamically that have a 'tenant_id' column
            $tables = [];
            foreach (DB::select('SHOW TABLES') as $tableInfo) {
                $tableName = array_values((array)$tableInfo)[0];
                if (Schema::hasColumn($tableName, 'tenant_id') && $tableName !== 'tenants') {
                    $tables[] = $tableName;
                }
            }

            foreach ($tables as $table) {
                $backup['tables'][$table] = DB::table($table)->where('tenant_id', $tenant->id)->get()->toArray();
            }

            // Pivot tables
            $studentIds = DB::table('students')->where('tenant_id', $tenant->id)->pluck('id')->toArray();
            
            if (!empty($studentIds)) {
                $backup['pivots']['parent_student'] = DB::table('parent_student')
                    ->whereIn('student_id', $studentIds)
                    ->get()
                    ->toArray();
                    
                $backup['pivots']['student_subject_overrides'] = DB::table('student_subject_overrides')
                    ->whereIn('student_id', $studentIds)
                    ->get()
                    ->toArray();
            } else {
                $backup['pivots']['parent_student'] = [];
                $backup['pivots']['student_subject_overrides'] = [];
            }

            $json = json_encode($backup, JSON_PRETTY_PRINT);
            $fileName = 'school-backup-' . $tenant->slug . '-' . now()->format('Y-m-d-His') . '.json';

            return response($json, 200, [
                'Content-Type' => 'application/json',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
            ]);
        } catch (\Throwable $e) {
            return redirect()->route('superadmin.tenants.edit', $tenant)
                ->withErrors(['error' => 'Backup failed: ' . $e->getMessage()]);
        }
    }

    public function importBackup(Tenant $tenant, Request $request)
    {
        $request->validate([
            'backup_file' => ['required', 'file', 'mimetypes:application/json,text/plain']
        ]);

        try {
            $file = $request->file('backup_file');
            $backup = json_decode(file_get_contents($file->getRealPath()), true);

            if (!$backup || !isset($backup['version']) || !isset($backup['tenant_id'])) {
                return redirect()->route('superadmin.tenants.edit', $tenant)
                    ->withErrors(['backup_file' => 'Invalid backup file structure.']);
            }

            // Start transaction
            DB::transaction(function() use ($tenant, $backup) {
                // Disable foreign key checks temporarily
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');

                // 1. Delete existing rows for this tenant
                $tables = [];
                foreach (DB::select('SHOW TABLES') as $tableInfo) {
                    $tableName = array_values((array)$tableInfo)[0];
                    if (Schema::hasColumn($tableName, 'tenant_id') && $tableName !== 'tenants') {
                        $tables[] = $tableName;
                    }
                }

                foreach ($tables as $table) {
                    DB::table($table)->where('tenant_id', $tenant->id)->delete();
                }

                // Delete pivot tables associated with our students
                $studentIds = DB::table('students')->where('tenant_id', $tenant->id)->pluck('id')->toArray();
                if (!empty($studentIds)) {
                    DB::table('parent_student')->whereIn('student_id', $studentIds)->delete();
                    DB::table('student_subject_overrides')->whereIn('student_id', $studentIds)->delete();
                }

                // 2. Restore school metadata
                if (isset($backup['tenant'])) {
                    $meta = $backup['tenant'];
                    unset($meta['id']);
                    unset($meta['slug']); // Keep the original slug to preserve custom domain setup
                    DB::table('tenants')->where('id', $tenant->id)->update($meta);
                }

                // 3. Restore tenant tables
                if (isset($backup['tables'])) {
                    foreach ($backup['tables'] as $table => $rows) {
                        if (Schema::hasTable($table) && !empty($rows)) {
                            // Ensure rows are formatted as arrays
                            $formattedRows = array_map(fn($row) => (array)$row, $rows);
                            
                            // Map all tenant_id values to the current school ID
                            foreach ($formattedRows as &$row) {
                                $row['tenant_id'] = $tenant->id;
                            }
                            
                            DB::table($table)->insert($formattedRows);
                        }
                    }
                }

                // 4. Restore pivot tables
                if (isset($backup['pivots'])) {
                    foreach ($backup['pivots'] as $table => $rows) {
                        if (Schema::hasTable($table) && !empty($rows)) {
                            $formattedRows = array_map(fn($row) => (array)$row, $rows);
                            DB::table($table)->insert($formattedRows);
                        }
                    }
                }

                // Re-enable foreign key checks
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            });

            return redirect()->route('superadmin.tenants.edit', $tenant)
                ->with('status', 'School database backup restored successfully. All school records have been updated.');
        } catch (\Throwable $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return redirect()->route('superadmin.tenants.edit', $tenant)
                ->withErrors(['backup_file' => 'Restore failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Keep the tenant's installed plugins in sync with their selected pricing plan.
     */
    private function syncTenantPluginsByPlan(Tenant $tenant, string $plan): void
    {
        // 1. Identify which components should be installed based on the selected plan
        $allowedSlugs = [];
        if ($plan === 'pro') {
            $allowedSlugs = ['cbt', 'homework', 'e-learning', 'whatsapp-bot', 'student-dashboard'];
        } elseif ($plan === 'enterprise') {
            // Enterprise gets all active marketplace components
            $allowedSlugs = MarketplaceComponent::where('is_active', true)->pluck('slug')->toArray();
        }

        // 2. Resolve target components in the database
        $componentsToInstall = MarketplaceComponent::whereIn('slug', $allowedSlugs)
            ->where('is_active', true)
            ->get();

        $installedIds = [];

        // 3. Activate and install missing allowed components
        foreach ($componentsToInstall as $component) {
            $setupFee = $tenant->plan === 'free' ? 0.00 : (float) $component->setup_fee;
            $usageFee = $tenant->plan === 'free' ? 0.00 : (float) $component->usage_fee_per_student;

            // Check if already installed (and not uninstalled)
            $pivot = $tenant->marketplaceComponents()
                ->where('marketplace_component_id', $component->id)
                ->wherePivotNull('uninstalled_at')
                ->first();

            if (!$pivot) {
                // Attach or update existing pivot
                $tenant->marketplaceComponents()->syncWithoutDetaching([
                    $component->id => [
                        'installed_at'             => now(),
                        'uninstalled_at'           => null,
                        'status'                   => 'active',
                        'setup_fee'                => $setupFee,
                        'usage_fee_per_student'    => $usageFee,
                        'price_paid'               => $setupFee,
                        'student_count_at_install' => 0,
                        'allowed_class_ids'        => [],
                    ]
                ]);

                $tenant->marketplaceComponents()->updateExistingPivot($component->id, [
                    'installed_at'             => now(),
                    'uninstalled_at'           => null,
                    'status'                   => 'active',
                    'setup_fee'                => $setupFee,
                    'usage_fee_per_student'    => $usageFee,
                    'price_paid'               => $setupFee,
                    'student_count_at_install' => 0,
                ]);

                // Increment installs count
                $component->increment('installs');

                // Create paid Setup Fee bill in the ledger
                if ($setupFee > 0) {
                    TenantPluginBill::create([
                        'tenant_id'                => $tenant->id,
                        'marketplace_component_id' => $component->id,
                        'bill_type'                => 'setup',
                        'term_name'                => null,
                        'session_name'             => null,
                        'student_count'            => null,
                        'setup_fee'                => $setupFee,
                        'usage_fee_per_student'    => 0,
                        'total_due'                => $setupFee,
                        'status'                   => 'paid',
                        'paid_at'                  => now(),
                    ]);
                }

                // Write Audit Log
                AuditLog::create([
                    'user_id' => auth()->id() ?? 1, // Superadmin or system
                    'action'  => 'plugin_installed_via_plan',
                    'model'   => 'MarketplaceComponent',
                    'model_id'=> $component->id,
                    'changes' => json_encode([
                        'slug'      => $component->slug,
                        'plan'      => $plan,
                        'setup_fee' => $setupFee,
                        'usage_fee' => $usageFee,
                    ]),
                ]);
            }

            $installedIds[] = $component->id;
        }

        // 4. Soft-uninstall active components that are NOT allowed under this plan
        $activeComponents = $tenant->marketplaceComponents()
            ->wherePivotNotNull('installed_at')
            ->wherePivotNull('uninstalled_at')
            ->get();

        foreach ($activeComponents as $activeComp) {
            if (!in_array($activeComp->id, $installedIds)) {
                // Soft-uninstall
                $tenant->marketplaceComponents()
                    ->wherePivot('marketplace_component_id', $activeComp->id)
                    ->updateExistingPivot($activeComp->id, [
                        'uninstalled_at' => now(),
                    ]);

                // Decrement install count if greater than 0
                if ($activeComp->installs > 0) {
                    $activeComp->decrement('installs');
                }

                // Write Audit Log
                AuditLog::create([
                    'user_id' => auth()->id() ?? 1,
                    'action'  => 'plugin_uninstalled_via_plan',
                    'model'   => 'MarketplaceComponent',
                    'model_id'=> $activeComp->id,
                    'changes' => json_encode([
                        'slug'           => $activeComp->slug,
                        'plan'           => $plan,
                        'uninstalled_at' => now()
                    ]),
                ]);
            }
        }
    }

    public function approveSubaccount(Tenant $tenant)
    {
        $settings = $tenant->settings ?? [];
        if (!isset($settings['payment_gateway'])) {
            $settings['payment_gateway'] = [];
        }
        $settings['payment_gateway']['subaccount_status'] = 'approved';
        $tenant->update(['settings' => $settings]);

        return redirect()->route('superadmin.tenants.edit', $tenant)
            ->with('status', 'School settlement account approved successfully. Online payment is now active.');
    }

    public function rejectSubaccount(Tenant $tenant)
    {
        $settings = $tenant->settings ?? [];
        if (isset($settings['payment_gateway'])) {
            $settings['payment_gateway']['subaccount_status'] = 'not_submitted';
        }
        $tenant->update(['settings' => $settings]);

        return redirect()->route('superadmin.tenants.edit', $tenant)
            ->with('status', 'School settlement account request has been reset.');
    }
}


