<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['nullable', 'string', 'max:255', 'unique:tenants,domain'],
            'plan' => ['required', 'string', 'in:free,pro,enterprise'],
            'status' => ['required', 'string', 'in:active,suspended,pending'],
            'max_students' => ['required', 'integer', 'min:1'],
            'max_teachers' => ['required', 'integer', 'min:1'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
        ]);

        $data['slug'] = Str::slug($data['name']) . '-' . strtolower(Str::random(5));

        Tenant::create($data);

        return redirect()->route('superadmin.tenants.index')
            ->with('status', 'School instance created successfully.');
    }

    public function edit(Tenant $tenant)
    {
        return view('superadmin.tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['nullable', 'string', 'max:255', 'unique:tenants,domain,' . $tenant->id],
            'plan' => ['required', 'string', 'in:free,pro,enterprise'],
            'status' => ['required', 'string', 'in:active,suspended,pending'],
            'max_students' => ['required', 'integer', 'min:1'],
            'max_teachers' => ['required', 'integer', 'min:1'],
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
            ->with('status', 'School instance deleted successfully.');
    }
}
