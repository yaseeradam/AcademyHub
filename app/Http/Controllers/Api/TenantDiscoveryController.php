<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;

class TenantDiscoveryController extends Controller
{
    public function show(Request $request, $slug)
    {
        $tenant = Tenant::where('slug', $slug)
            ->where('status', 'active')
            ->first();

        if (!$tenant) {
            return response()->json([
                'message' => 'School instance not found or is currently inactive.',
            ], 404);
        }

        return response()->json([
            'id' => $tenant->id,
            'name' => $tenant->name,
            'slug' => $tenant->slug,
            'domain' => $tenant->domain,
            'logo_url' => $tenant->logo ? asset('storage/' . $tenant->logo) : null,
            'primary_color' => $tenant->primary_color ?? '#4f46e5', // default Indigo-600 color
            'status' => $tenant->status,
        ]);
    }
}
