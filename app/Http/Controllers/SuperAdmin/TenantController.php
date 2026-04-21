<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
        return view('superadmin.tenants.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'domain'        => ['nullable', 'string', 'max:255', 'unique:tenants,domain'],
            'plan'          => ['required', 'string', 'in:free,pro,enterprise'],
            'status'        => ['required', 'string', 'in:active,suspended,pending'],
            'max_students'  => ['required', 'integer', 'min:1'],
            'max_teachers'  => ['required', 'integer', 'min:1'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],

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

        $tenant = Tenant::create($data);

        // Create the admin user and associate with the tenant
        if ($adminData) {
            $adminData['tenant_id'] = $tenant->id;
            User::create($adminData);
        }

        $message = 'School instance created successfully.';
        if ($adminData) {
            $message .= ' Admin account created for ' . $adminData['email'] . '.';
        }

        return redirect()->route('superadmin.tenants.index')
            ->with('status', $message);
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
        $tenant->delete();

        return redirect()->route('superadmin.tenants.index')
            ->with('status', 'School instance deleted.');
    }
}
