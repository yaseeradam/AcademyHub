<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
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

            file_put_contents($hostsFile, $contents . PHP_EOL . "127.0.0.1      {$host} #laragon magic!" . PHP_EOL);
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
        return view('superadmin.tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'domain'        => ['nullable', 'string', 'max:255', 'unique:tenants,domain,' . $tenant->id],
            'plan'          => ['required', 'string', 'in:free,pro,enterprise'],
            'status'        => ['required', 'string', 'in:active,suspended,pending'],
            'max_students'  => ['required', 'integer', 'min:1'],
            'max_teachers'  => ['required', 'integer', 'min:1'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
        ]);

        $tenant->update($data);

        return redirect()->route('superadmin.tenants.index')
            ->with('status', 'School instance updated successfully.');
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
}
