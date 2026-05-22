<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceComponent;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    public function index()
    {
        $components = MarketplaceComponent::orderBy('name')->get();
        return view('superadmin.marketplace.index', compact('components'));
    }

    public function create()
    {
        $component = new MarketplaceComponent();
        return view('superadmin.marketplace.form', compact('component'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                  => 'required|string|max:255',
            'slug'                  => 'required|string|max:255|unique:marketplace_components,slug',
            'route_name'            => 'nullable|string|max:255',
            'setup_fee'             => 'required|numeric|min:0',
            'usage_fee_per_student' => 'required|numeric|min:0',
            'description'           => 'nullable|string',
            'icon'                  => 'nullable|string|max:255',
            'is_active'             => 'boolean',
        ]);

        $data['is_active'] = $request->has('is_active');
        
        $isPaid = $request->input('pricing_type') === 'paid';
        $data['setup_fee']             = $isPaid ? (float) $request->input('setup_fee', 0) : 0.0;
        $data['usage_fee_per_student'] = $isPaid ? (float) $request->input('usage_fee_per_student', 0) : 0.0;
        $data['price']                 = $data['setup_fee'];

        MarketplaceComponent::create($data);

        return redirect()->route('superadmin.marketplace.index')->with('success', 'Plugin created successfully.');
    }

    public function edit(MarketplaceComponent $marketplace)
    {
        return view('superadmin.marketplace.form', ['component' => $marketplace]);
    }

    public function update(Request $request, MarketplaceComponent $marketplace)
    {
        $data = $request->validate([
            'name'                  => 'required|string|max:255',
            'slug'                  => 'required|string|max:255|unique:marketplace_components,slug,' . $marketplace->id,
            'route_name'            => 'nullable|string|max:255',
            'setup_fee'             => 'required|numeric|min:0',
            'usage_fee_per_student' => 'required|numeric|min:0',
            'description'           => 'nullable|string',
            'icon'                  => 'nullable|string|max:255',
            'is_active'             => 'boolean',
        ]);

        $data['is_active'] = $request->has('is_active');

        $isPaid = $request->input('pricing_type') === 'paid';
        $data['setup_fee']             = $isPaid ? (float) $request->input('setup_fee', 0) : 0.0;
        $data['usage_fee_per_student'] = $isPaid ? (float) $request->input('usage_fee_per_student', 0) : 0.0;
        $data['price']                 = $data['setup_fee'];

        $marketplace->update($data);

        return redirect()->route('superadmin.marketplace.index')->with('success', 'Plugin updated successfully.');
    }

    public function destroy(MarketplaceComponent $marketplace)
    {
        $marketplace->delete();
        return redirect()->route('superadmin.marketplace.index')->with('success', 'Marketplace component deleted successfully.');
    }
}
