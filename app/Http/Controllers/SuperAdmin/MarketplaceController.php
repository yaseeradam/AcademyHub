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
            'price'                 => 'required|numeric|min:0',
            'setup_fee'             => 'required|numeric|min:0',
            'usage_fee_per_student' => 'required|numeric|min:0',
            'description'           => 'nullable|string',
            'icon'                  => 'nullable|string|max:10000',
            'is_active'             => 'boolean',
            'screenshot_count'      => 'nullable|integer|min:1|max:5',
        ]);

        $data['is_active'] = $request->has('is_active');
        
        $isPaid = $request->input('pricing_type') === 'paid';
        $data['price']                 = $isPaid ? (float) $request->input('price', 0) : 0.0;
        $data['setup_fee']             = $isPaid ? (float) $request->input('setup_fee', 0) : 0.0;
        $data['usage_fee_per_student'] = $isPaid ? (float) $request->input('usage_fee_per_student', 0) : 0.0;

        // Process screenshots metadata
        $screenshotsMetadata = [];
        $screenshotCount = (int) $request->input('screenshot_count', 3);

        for ($i = 0; $i < 5; $i++) {
            $title = $request->input("screenshots.{$i}.title") ?: "Screenshot " . ($i + 1);
            $filename = $request->input("screenshots.{$i}.filename") ?: "";

            // Handle file upload if present
            if ($request->hasFile("screenshot_files.{$i}")) {
                $file = $request->file("screenshot_files.{$i}");
                $extension = $file->getClientOriginalExtension();
                $filename = $request->input('slug') . '-screenshot-' . ($i + 1) . '-' . time() . '.' . $extension;
                
                $destinationPath = public_path('images');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                $file->move($destinationPath, $filename);
            }

            $screenshotsMetadata[$i] = [
                'title' => $title,
                'filename' => $filename,
            ];
        }

        $data['screenshot_count'] = $screenshotCount;
        $data['screenshots_metadata'] = $screenshotsMetadata;

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
            'price'                 => 'required|numeric|min:0',
            'setup_fee'             => 'required|numeric|min:0',
            'usage_fee_per_student' => 'required|numeric|min:0',
            'description'           => 'nullable|string',
            'icon'                  => 'nullable|string|max:10000',
            'is_active'             => 'boolean',
            'screenshot_count'      => 'nullable|integer|min:1|max:5',
        ]);

        $data['is_active'] = $request->has('is_active');

        $isPaid = $request->input('pricing_type') === 'paid';
        $data['price']                 = $isPaid ? (float) $request->input('price', 0) : 0.0;
        $data['setup_fee']             = $isPaid ? (float) $request->input('setup_fee', 0) : 0.0;
        $data['usage_fee_per_student'] = $isPaid ? (float) $request->input('usage_fee_per_student', 0) : 0.0;

        // Process screenshots metadata
        $screenshotsMetadata = [];
        $screenshotCount = (int) $request->input('screenshot_count', 3);
        
        $existingMetadata = $marketplace->screenshots_metadata;
        if (is_string($existingMetadata)) {
            $existingMetadata = json_decode($existingMetadata, true) ?: [];
        }
        $existingMetadata = is_array($existingMetadata) ? $existingMetadata : [];

        for ($i = 0; $i < 5; $i++) {
            $title = $request->input("screenshots.{$i}.title") ?: "Screenshot " . ($i + 1);
            $filename = $request->input("screenshots.{$i}.filename") ?: "";

            // Handle file upload if present
            if ($request->hasFile("screenshot_files.{$i}")) {
                $file = $request->file("screenshot_files.{$i}");
                $extension = $file->getClientOriginalExtension();
                $filename = $marketplace->slug . '-screenshot-' . ($i + 1) . '-' . time() . '.' . $extension;
                
                $destinationPath = public_path('images');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }
                $file->move($destinationPath, $filename);
            }

            // Fallback to existing filename if none provided
            if (empty($filename) && isset($existingMetadata[$i]['filename'])) {
                $filename = $existingMetadata[$i]['filename'];
            }

            $screenshotsMetadata[$i] = [
                'title' => $title,
                'filename' => $filename,
            ];
        }

        $data['screenshot_count'] = $screenshotCount;
        $data['screenshots_metadata'] = $screenshotsMetadata;

        $marketplace->update($data);

        return redirect()->route('superadmin.marketplace.index')->with('success', 'Plugin updated successfully.');
    }

    public function destroy(MarketplaceComponent $marketplace)
    {
        $marketplace->delete();
        return redirect()->route('superadmin.marketplace.index')->with('success', 'Marketplace component deleted successfully.');
    }
}
