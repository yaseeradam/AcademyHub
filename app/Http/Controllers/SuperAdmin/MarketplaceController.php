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
            'name'       => 'required|string|max:255',
            'slug'       => 'required|string|max:255|unique:marketplace_components,slug',
            'route_name' => 'nullable|string|max:255',
            'price'      => 'required|numeric|min:0',
            'description'=> 'nullable|string',
            'icon'       => 'nullable|string|max:255',
            'is_active'  => 'boolean',
        ]);

        $data['is_active'] = $request->has('is_active');
        $data['price']     = $request->input('pricing_type') === 'paid' ? $request->input('price', 0) : 0;

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
            'name'       => 'required|string|max:255',
            'slug'       => 'required|string|max:255|unique:marketplace_components,slug,' . $marketplace->id,
            'route_name' => 'nullable|string|max:255',
            'price'      => 'required|numeric|min:0',
            'description'=> 'nullable|string',
            'icon'       => 'nullable|string|max:255',
            'is_active'  => 'boolean',
        ]);

        $data['is_active'] = $request->has('is_active');
        $data['price']     = $request->input('pricing_type') === 'paid' ? $request->input('price', 0) : 0;

        $marketplace->update($data);

        return redirect()->route('superadmin.marketplace.index')->with('success', 'Plugin updated successfully.');
    }

    public function destroy(MarketplaceComponent $marketplace)
    {
        $marketplace->delete();
        return redirect()->route('superadmin.marketplace.index')->with('success', 'Marketplace component deleted successfully.');
    }
}
